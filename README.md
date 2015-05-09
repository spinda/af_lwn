# af_lwn

Little plugin for [Tiny Tiny RSS](https://tt-rss.org/) to extract the full content of [LWN](https://lwn.net/) feed items, including subscriber-only content.

If you want to extract subscriber-only content, configure `LWN_USER` and `LWN_PASS` in your config.php, like so:

```php
	define('LWN_USER', '<username>');
	define('LWN_PASS', '<password>');
```

