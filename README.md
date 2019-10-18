<a id="status-image-popup" title="Latest push build on default branch: " name="status-images" class="pointer open-popup">
    <img src="https://travis-ci.org/coExp/wUnderBar.svg?branch=master" alt="build:">
</a>
<a href="https://scrutinizer-ci.com/g/coExp/wUnderBar/?branch=master" rel="nofollow noindex noopener external">
    <img src="https://scrutinizer-ci.com/g/coExp/wUnderBar/badges/quality-score.png?b=master" title="Scrutinizer Code Quality" alt="Code Quality">
</a>

<a href="https://scrutinizer-ci.com/g/coExp/wUnderBar/?branch=master" rel="nofollow noindex noopener external">
    <img src="https://scrutinizer-ci.com/g/coExp/wUnderBar/badges/coverage.png?b=master" alt="Code Coverage">
</a>

<a href="https://scrutinizer-ci.com/g/coExp/wUnderBar/?branch=master" rel="nofollow noindex noopener external">
    <img src="https://scrutinizer-ci.com/g/coExp/wUnderBar/badges/code-intelligence.svg?b=master" alt="Code Intelligence Status">
</a>

<img src="https://camo.githubusercontent.com/9df910d1bb7903aea32573be62a9ad2554b6ce11/68747470733a2f2f706f7365722e707567782e6f72672f616c6963656d616a6572652f776f6e6465726c616e642d7468726561642f6c6963656e7365" alt="License">

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
