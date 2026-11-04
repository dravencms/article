# Dravencms article module

This is a simple article module for dravencms

## Instalation

The best way to install dravencms/article is using  [Composer](http://getcomposer.org/):


```sh
$ composer require dravencms/article
```

Then you have to register extension in `config.neon`.

```yaml
extensions:
	dravencms.article: Dravencms\Article\DI\ArticleExtension
```

## Admin authorization and mutations

Administration uses User 3.1, Admin 3.2 and Application 0.3 guards. Screens and write callbacks enforce the existing provider-defined permissions server-side, including direct URLs and bulk callbacks. Mutating signals require POST and a session-bound action token; synchronize the matching admin assets when upgrading. See the User package's `user:security:migrate` instructions for the password-reset schema change.


## Contextual file delivery

This release requires Dravencms File 3 / Nette Files 3. Image URLs include a specific
file resolver and attachment reference; templates no longer pass bare files. Published
attachments are public-cacheable; admin previews of unpublished content require the
plugin's edit permission and are private. Original files and generated images stay
outside the document root. Install the private-storage configuration and run File's
checksum/storage migrations before serving this release. See the File package README.


## Variant configuration (3.1)

This package declares its own variants in `dravencms.config.neon`; Nette merges the
`files.variants` maps. Definitions use named `width`, `height` and `resizeMode` fields.
`resizeMode` defaults to `fit`; an omitted/null dimension is unconstrained, and at
least one dimension is required. On upgrade, replace old positional definitions
and remove variants owned by other packages from the File configuration. Project
variants belong in project configuration. Update the matching File, Nette Files
and installed Gallery/Article/Partner/Carousel releases together. No additional
database or storage migration is needed for this configuration change.
