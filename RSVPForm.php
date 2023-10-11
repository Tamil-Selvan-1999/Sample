<?php

/**
 * @file
 * A form to collect an email address for RSVP details
 */

namespace Drupal\rsvplist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class RSVPForm extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'rsvplist_email_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $node = \Drupal::routeMatch()->getParameter('node');

        if (!(is_null($node))) {
            $nid = $node->id();
        }
        else {
            $nid = 0;
        }

        $form['email'] = [
            '#type' => 'textfield',
            '#title' => t('Email Address'),
            '#size' => 25,
            '#description' => t("We will send updates to the email address you provide"),
            '#required' => TRUE,
        ];
        $form['submit'] = [
            '#type' => 'submit',
            '#value' => t('RSVP'),
        ];
        $form['nid'] = [
            '#type' => 'hidden',
            '#value' => $nid,
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $value = $form_state->getValue('email');
        if (!(\Drupal::service('email.validator')->isValid($value))) {
            $form_state->setErrorByName('email', $this->t('It appears that %mail is not a valid email. Please try again', ['%mail' => $value]));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        //$submitted_email = $form_state->getValue('email');
        //$this->messenger()->addMessage(t("The form is working! You entered @entry.", ['@entry' => $submitted_email]));

        try {
            $uid = \Drupal::currentUser()->id();
            //$full_user = \Drupal\user\Entity\User::load((\Drupal::currentUser()->id()));
            $nid = $form_state->getValue('nid');
            $email = $form_state->getValue('email');
            $current_time = \Drupal::time()->getRequestTime();

            $query = \Drupal::database()->insert('rsvplist');

            $query->fields([
                'uid',
                'nid',
                'mail',
                'created',
            ]);

            $query->values([
                $uid,
                $nid,
                $email,
                $current_time,
            ]);

            $query->execute();

            \Drupal::messenger()->addMessage(t('Thank you for your RSVP, you are on the list for the event'));

        } catch(\Exception $e) {
            \Drupal::messenger()->addError(t('Thank you for your RSVP, you are on the list for the event'));
        }
    }
}