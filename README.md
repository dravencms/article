# Dravencms article module

This is a simple article module for dravencms

## Instalation

The best way to install dravencms/article is using  [Composer](http://getcomposer.org/):


```sh
$ composer require dravencms/article:@dev
```

Then you have to register extension in `config.neon`.

```yaml
extensions:
	article: Dravencms\Article\DI\ArticleExtension
```
