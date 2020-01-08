<?php


namespace Drupal\subscribe\Controller;

use Drupal\Core\Controller\ControllerBase;


class MailController extends ControllerBase
{

    /**
     * {@inheritdoc}
     */
    public function content()
    {

        $query = \Drupal::entityQuery('webform_submission')
            ->condition('webform_id', 'subscribe');
        $result = $query->execute();
        $submission_data = [];
        foreach ($result as $item) {
            $submission = \Drupal\webform\Entity\WebformSubmission::load($item);
            $submission_data[] = $submission->getData();
        }
        //kint($submission_data);


        $mailManager = \Drupal::service('plugin.manager.mail');
        $sitename = \Drupal::config('system.site')->get('name');
        $langcode = \Drupal::config('system.site')->get('langcode');
        $module = 'subscribe';
        $key = 'my_key';
        $reply = NULL;
        $send = TRUE;
        $to = 'ridha.rahmi@hotmail.com';


        $params['message'] = $this->t('Your wonderful message about @sitename', array('@sitename' => $sitename));
        $params['subject'] = $this->t('Message subject');
        $params['options']['username'] = 'Ridha Rahmi';
        $params['options']['url'] = drupal_get_path('module', 'subscribe');
        $params['options']['title'] = $this->t('Your wonderful title');
        $params['options']['footer'] = $this->t('Your wonderful footer');

        $result = $mailManager->mail($module, $key, $to, $langcode, $params, null, $send);
//        foreach ($submission_data as $to){
//            $result = $mailManager->mail($module, $key, $to['email'], $langcode, $params, $reply, $send);
//        }
        //kint($langcode);


        if ($result['result'] == TRUE) {
            $this->messenger()->addMessage($this->t('Your message has been sent.'));
            return [
                "#type" => "markup",
                "#markup" => "Your message has been sent."
            ];
        } else {
            $this->messenger()->addMessage($this->t('There was a problem sending your message and it was not sent.'), 'error');

            return [
                "#type" => "markup",
                "#markup" => "There was a problem sending your message and it was not sent."
            ];
        }
    }

}

