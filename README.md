
* Send Mail Subscribe Drupal 8

* Install the SMTP module
  * Step 1: Download the SMTP module for Drupal: https://drupal.org/project/smtp
  
  * Step 2: Extract to /sites/all/modules

  * Step 3: Enable the module on Admin - Modules

  * Step 4 (for Drupal 6 only): you have to install the PHPMailer package from Codeworx Tech., version supported is below 2.2.1. Place it in a directory named "phpmailer" in /sites/all/modules/smtp

* Configure with Gmail account
  * Step 1: Pls go to Admin - Configuration - SMTP Authentication Support

  * Step 2: On Install Options, please choose On

  * Step 3: On SMTP server setting, please enter

    * SMTP server: smtp.gmail.com
    * SMTP backup server: <blank>
    * SMTP port: 465
    * Use encrypted protocol: Use SSL
     If the combination of SSL and port 465 does not work, pls try TLS and port 587

  * Step 4: On SMTP Authentication: Enter user name and password of your Gmail account.

  * Step 5: On Email options: Enter your email from address (could be noreply@yourwebsite.com) and email from name (could be your site name).

  * Step 6: On Send test email: enter a email to test whether the module is working. Click on Save and go to your inbox to check. If there is an testing email then it is fine. 



* 
*
* Ridha Rahmi
* Web Developer Drupal

