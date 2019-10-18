<a id="status-image-popup" title="Latest push build on default branch: " name="status-images" class="pointer open-popup">
    <img src="https://travis-ci.org/coExp/wUnderBar.svg?branch=master" alt="build:">
</a>


wUnderBar
====================

This package helps to implement Symfony/ProgressBar on multiple line. 
Progressbar is print on stdout, and not stdError by default


Examples
--------

Set two progressbar: 
```php
 $mb = (new MultipleBar($this->output))
    ->setTitle('wUnderBar Example #1')
    ->addProgressBarByName(['Master', 'Child']);
```

Advance:
```php
$mb->getProgressBarByName('Child')->advance();
$mb->getProgressBarByName('Master')->advance();
$mb->show();
```

Finish by erasing bar:
```php
$mb->erase();
```
