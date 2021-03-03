### Content
This repo contains the new Website files.
It includes also a PHP mailer script.

### How to update the website
Run

```make update```

the command will `git pull` the last version from the repo, and will then run `composer install` where PHP is needed.


### Requirements:
  - PHP version >= 7.4
  - Make sure `./contact-form-handler/logs` is writable by the web user
