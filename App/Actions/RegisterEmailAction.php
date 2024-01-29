<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Actions;

use Hleb\Helpers\HostHelper;
use Hleb\Static\Log;
use Hleb\Static\Request;
use Hleb\Static\Router;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\CurrentUser;
use Phphleb\Hlogin\App\Data\EmailRecoveryHash;
use Phphleb\Hlogin\App\Exceptions\HloginMailException;
use Phphleb\Hlogin\App\Helpers\EmailHelper;
use Phphleb\Hlogin\App\Mail\ConfirmRegisterEmail;
use Phphleb\Hlogin\App\Models\UserModel;
use Phphleb\Hlogin\App\RegType;

/**
 * Resending the registration email confirmation,
 * if the previous one has expired.
 * If the deadline has not expired, a duplicate will be sent.
 *
 * Повторная отправка подтверждения регистрационного письма,
 * если у предыдущего истёк срок действия.
 * Если срок не истёк, то высылает дубликат.
 *
 * @internal
 */
final class RegisterEmailAction extends AbstractBaseAction
{
    /** @inheritDoc */
    #[\Override]
    public function execute(array $params): array
    {
        // Checking the connection to the database.
        // Проверка подключения к базе данных.
        if (!UserModel::checkTableUsers()) {
            return $this->getCustomMessage('No data was received from the database', 'Problem getting data from a table with users');
        }

        $user = CurrentUser::get();
        if (!$user) {
            return $this->getCustomMessage('user_not_reg', 'Error! User not found.');
        }
        if ($user['regtype'] < RegType::UNDEFINED_USER) {
            return $this->getCustomMessage('deleted_user', 'The user with this E-mail has been deleted or blocked');
        }
        if ($user['newemail'] || $user['confirm']) {
            return $this->getCustomMessage('u_error', 'Error! The user has already registered.');
        }
        $hash = $user['hash'];
        if (!$hash || !EmailRecoveryHash::check($user['id'], $user['email'], $hash, $user['hash'])) {
            $hash = EmailRecoveryHash::generate($user['id'], $user['email']);
            UserModel::setCells('id', $user['id'], ['hash' => $hash]);
        }

        $senderName = Request::getHost();
        if (!empty($config['mail']['sender-name'])) {
            $senderName = $config['mail']['sender-name'];
        }
        $header = AuthLang::trans($this->lang, 'email_confirm_header');
        $title = $senderName . ': ' . $header;
        $secureLink = Router::address(
                routeName: 'hlogin.action.page',
                replacements: ['lang' => $this->lang, 'action' => 'confirm'],
                endPart: false,
            ) . '?code=' . $hash;

        $mailFrom = EmailHelper::default(Request::getHost());
        if (!empty($config['mail']['from'])) {
            $mailFrom = $config['mail']['from'];
        }

        try {
            (new ConfirmRegisterEmail(
                $senderName,
                $mailFrom,
                EmailHelper::convert($user['email']),
                $this->lang,
                onlyToFile: HostHelper::isLocalhost(Request::getHost()),
            ))->send($title, $header, $secureLink, null);
        } catch (HloginMailException $e) {
            Log::error($e->getMessage(), ['email' => $user['email'], 'title' => $title, 'name' => $senderName]);

            return $this->getCustomMessage('u_error', $e->getMessage(), true);
        }

        return $this->getCustomMessage('user_new_email_text', 'Successful update');
    }

    private function getCustomMessage(string $tag, string $systemMessage, bool $validType = false): array
    {
        return $this->getSuccessResponse(
            [
                'data' => [
                    'id' => AuthLang::trans($this->lang, 'profile_page'),
                    'value' => AuthLang::trans($this->lang, $tag),
                ],
                'action' => ['type' => $validType ? 'CustomEmailMessage' : 'CustomMessage'],
                'captcha' => false,
            ],
            $systemMessage,
        );
    }
}