<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\Deployment;

use Hleb\Helpers\StorageLibConfigurator;
use Hleb\Main\Console\Commands\Deployer\DeploymentLibInterface;
use Hleb\Static\Settings;
use RuntimeException;
use JsonException;
use Phphleb\Adminpan\Deployment\StructureMigration;
use Phphleb\Hlogin\App\RegData;
use Phphleb\Spreader\Transfer;
use Phphleb\Updater\AddAction;
use Phphleb\Updater\RemoveAction;

class StartForHleb implements DeploymentLibInterface
{
    private bool $noInteraction = false;

    private bool $quiet = false;

    /**
     * @param array $config - configuration for deploying libraries,
     *                        sample in updater.json file.
     *                      - конфигурация для развертывания библиотек,
     *                        образец в файле updater.json.
     */
    public function __construct(private readonly array $config)
    {
    }

    /**
     * @inheritDoc
     */
    public function noInteraction(): void
    {
        $this->noInteraction = true;
    }

    /**
     * @inheritDoc
     */
    public function help(): string|false
    {
        return 'Adding/removing a registration module in the project.';
    }

    /**
     * @inheritDoc
     *
     * @throws JsonException
     */
    public function add(): int
    {
        $action = new AddAction($this->config, $this->noInteraction, $this->quiet);
        $code = $action->run();
        if ($code) {
            return $code;
        }

        // Get the final value of the settings.
        // Получение конечного значение настроек.
        $options = $action->getReadOptions();

        // Update configuration data.
        // Обновление данных конфигурации.
        $helper = new StorageLibConfigurator($this->config['component']);
        $addDesign = $helper->setConfigOption('config.json', 'design', $options['hlogin']);
        $addVersion = $helper->setConfigOption('config.json', 'version', RegData::API_VERSION, 'int');

        $code = (int)\in_array(false, [$addDesign, $addVersion]);
        if ($code) {
            throw new RuntimeException('Failed to update configuration file `config.json`.');
        }
        (new Transfer())->update('phphleb/hlogin', 'config');

        // Adding a configuration file with settings for the pages of the administrative panel.
        // Добавление конфигурационного файла с настройками страниц административной панели.
        $originPath = Settings::getPath('@library/hlogin/hleb-project/structure/hlogin.php');
        $targetPath = Settings::getPath('@/config/structure/hlogin.php');

        return (new StructureMigration())->add($originPath, $targetPath, $this->quiet);
    }

    /**
     * @inheritDoc
     */
    public function remove(): int
    {
        return (new RemoveAction($this->config, $this->noInteraction, $this->quiet))->run();
    }

    /**
     * Returns the library class loading map required for deployment
     * in the format: classname => realpath.
     * This is needed in case for some reason these classes
     * are not supported by the current class loaders.
     * In most cases, returning an empty array will suffice.
     *
     * Возвращает необходимую для развертывания карту загрузки
     * классов библиотеки в формате: classname => realpath.
     * Это нужно в случае, если по какой-то причине эти классы
     * не поддерживаются текущими загрузчиками классов.
     * В большинстве случаев будет достаточно вернуть пустой массив.
     *
     * @inheritDoc
     */
    public function classmap(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function quiet(): void
    {
        $this->quiet = true;
    }
}