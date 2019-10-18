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
