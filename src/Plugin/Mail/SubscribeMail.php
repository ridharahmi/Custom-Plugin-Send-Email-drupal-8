<?php
/**
 * @file
 * Contains \Drupal\subscribe\Plugin\Mail\SubscribeMail .
 */

namespace Drupal\subscribe\Plugin\Mail;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Mail\Plugin\Mail\PhpMail;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Site\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Defines the plugin Mail.
 *
 * @Mail(
 *   id = "subscribe_mail",
 *   label = @Translation("Subscribe HTML mailer"),
 *   description = @Translation("Sends an HTML email")
 * )
 */
class SubscribeMail extends PHPMail implements MailInterface, ContainerFactoryPluginInterface
{

    /**
     * @var \Drupal\Core\Render\Renderer;
     */
    protected $renderer;
    /**
     * The configuration factory.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected $configFactory;

    /**
     * SubscribeMail constructor.
     *
     * @param \Drupal\Core\Render\Renderer $renderer
     *   The service renderer.
     */
    function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->configFactory = \Drupal::configFactory();
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static(
            $container->get('renderer')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $message)
    {
        $render = [
            '#theme' => 'mail',
            '#message' => $message,
        ];
        $message['body'] = $this->renderer->renderRoot($render);
        return $message;
    }


    /**
     * {@inheritdoc}
     */
    public function mail(array $message) {
        // If 'Return-Path' isn't already set in php.ini, we pass it separately
        // as an additional parameter instead of in the header.
        if (isset($message['headers']['Return-Path'])) {
            $return_path_set = strpos(ini_get('sendmail_path'), ' -f');
            if (!$return_path_set) {
                $message['Return-Path'] = $message['headers']['Return-Path'];
                unset($message['headers']['Return-Path']);
            }
        }
        $mimeheaders = [];
        foreach ($message['headers'] as $name => $value) {
            $mimeheaders[] = $name . ': ' . Unicode::mimeHeaderEncode($value);
        }
        $line_endings = Settings::get('mail_line_endings', PHP_EOL);
        // Prepare mail commands.
        $mail_subject = Unicode::mimeHeaderEncode($message['subject']);
        // Note: email uses CRLF for line-endings. PHP's API requires LF
        // on Unix and CRLF on Windows. Drupal automatically guesses the
        // line-ending format appropriate for your system. If you need to
        // override this, adjust $settings['mail_line_endings'] in settings.php.
        $mail_body = preg_replace('@\r?\n@', $line_endings, $message['body']);
        // For headers, PHP's API suggests that we use CRLF normally,
        // but some MTAs incorrectly replace LF with CRLF. See #234403.
        $mail_headers = implode("\n", $mimeheaders);

        $request = \Drupal::request();

        // We suppress warnings and notices from mail() because of issues on some
        // hosts. The return value of this method will still indicate whether mail
        // was sent successfully.
        if (!$request->server->has('WINDIR') && strpos($request->server->get('SERVER_SOFTWARE'), 'Win32') === FALSE) {
            // On most non-Windows systems, the "-f" option to the sendmail command
            // is used to set the Return-Path. There is no space between -f and
            // the value of the return path.
            // We validate the return path, unless it is equal to the site mail, which
            // we assume to be safe.
            $site_mail = $this->configFactory->get('system.site')->get('mail');
            $additional_headers = isset($message['Return-Path']) && ($site_mail === $message['Return-Path'] || static::_isShellSafe($message['Return-Path'])) ? '-f' . $message['Return-Path'] : '';
            $mail_result = @mail(
                $message['to'],
                $mail_subject,
                $mail_body,
                $mail_headers,
                $additional_headers
            );
        }
        else {
            // On Windows, PHP will use the value of sendmail_from for the
            // Return-Path header.
            $old_from = ini_get('sendmail_from');
            ini_set('sendmail_from', $message['Return-Path']);
            $mail_result = @mail(
                $message['to'],
                $mail_subject,
                $mail_body,
                $mail_headers
            );
            ini_set('sendmail_from', $old_from);
        }

        return $mail_result;
    }
}
