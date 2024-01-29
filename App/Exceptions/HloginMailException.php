<?php

namespace Phphleb\Hlogin\App\Exceptions;

/**
 * Exceptions thrown when sending a letter.
 * Messages must be unique in that they are visible
 * to the user and do not contain sensitive data
 * or other information that could lead to vulnerabilities.
 *
 * Исключения, вызванные при отправке письма.
 * Особенностью сообщений должно являться то,
 * что они видимы пользователю и не содержат
 * конфиденциальных данных или иную информацию,
 * могущую привести к уязвимостям.
 */
class HloginMailException extends \RuntimeException
{
}