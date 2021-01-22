# Image Storage

:city_sunset: Extension for [68publishers/file-storage](https://github.com/68publishers/file-storage) that can generate images on-the-fly and more!

Based on [thephpleague/flysystem](https://github.com/thephpleague/flysystem) and [intervention/image](https://github.com/Intervention/image).

## Installation

The best way to install 68publishers/image-storage is using Composer:

```bash
composer require 68publishers/image-storage
```

## Integration into Nette Framework

Firstly, please read a documentation of [68publishers/file-storage](https://github.com/68publishers/file-storage/blob/master/README.md).

### File storage configuration example

Each image-storage is based on file-storage. so firstly we need to register our storage under file-storage extension.
Here is an example configuration:

```neon
68publishers.file_storage:
    storages:
        local:
            config:
                base_path: /images
                signature_key: my-arbitrary-private-key
                allowed_pixel_density: [ 1, 2, 3 ]
                allowed_resolutions: [ 50x50, 200x200, 300x300, 200x, x200 ]
                allowed_qualities: [ 50, 80, 100 ]
                encode_quality: 90
                cache_max_age: 31536000
            filesystem:
                adapter: League\Flysystem\Local\LocalFilesystemAdapter(%wwwDir%/images)
            assets:
                assets/image/noimage.png: noimage/default.png # copy our default no-image
                assets/image/noimage_user.png: noimage/user.png # copy our default no-image
```

#### Storage config options

name | type | default | description
---- | ---- | ---- | ----
base_path | string | `''` | Base path to a directory where the files are accessible.
host | null or string | `null` | Hostname, use if the files are not stored locally or if you want to generate an absolute links
version_parameter_name | string | `_v` | A query parameter's name used for a file's version (just for a cache).
signature_parameter_name | string | `_s` | A query parameter's name used for a signature token.
signature_key | null or string | `null` | Your private signature key used for a token encryption. Signatures in requests are checked and validated only if this parameter is set.
signature_algorithm | string | `sha256` | An algorithm used for encryption of signatures (HMAC).
modifier_separator | string | `,` | A separator for modifier definitions in a path. For example if you set this parameter as `;` then a modifier string in a path will look like this: `w:100;o:auto`.
modifier_assigner | string | `:` | An assigner for modifier definitions in a path. For example if you set this parameter as `=` then a modifier string in a path will look like this: `w=100,o=auto`.
allowed_pixel_density | int[] or float[] | `[]` | An array of allowed pixed densities. The validation is enabled when the array is not empty.
allowed_resolutions | string[] | `[]` | An array of allowed resolutions like `100x`, `x200` or `100x200`. The validation is enabled when the array is not empty.
allowed_qualities | int[] | `[]` | An array of allowed qualities. The validation is enabled when the array is not empty.
encode_quality | int | `90` | An encode quality for cached images.
cache_max_age | int | `31536000` | The maximum cache age in seconds. The value is used for HTTP headers Cache-Control and Expires.

### Image storage configuration example

Now we can register a `ImageStorageExtension` and define the `local` image-storage:

```neon
extensions:
    68publishers.image_storage: SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\ImageStorageExtension

68publishers.image_storage:
    driver: gd # "gd" or "imagick", default is "gd"
    storages:
        local:
            source_filesystem:
                adapter: League\Flysystem\Local\LocalFilesystemAdapter(%appDir%/../private-data/images)
                config: # an optional config for source filesystem adapter
            server: local # "local" or "external", default is "local"
            no_image:
                default: noimage/default.png
                user: noimage/user.png
            no_image_patterns:
                user: '^user_avatar\/' # the noimage "user" will be used for missing files with paths that matches this regex
            presets:
                my_preset: {w: 150, ar: '2x1.5'}
```

### Basic usage

Basic usage is similar to usage of the `file-storage`.

#### Persisting files

Files persisting is almost the same as persisting in the `file-storage` but source images are stored without a file extension.

```php
<?php

/** @var \SixtyEightPublishers\ImageStorage\ImageStorageInterface $storage */

# Create resource from a local file:
$resource = $storage->createResourceFromLocalFile(
    $storage->createPathInfo('test/my-image.jpeg'),
    __DIR__ . '/path/to/my-image.jpeg'
);

$storage->save($resource);

# Create resource from a file that is stored in storage:
$resource = $storage->createResource(
    $storage->createPathInfo('test/my-image')
);

# Copy to the new location
$storage->save($resource->withPathInfo(
    $storage->createPathInfo('test/my-image-2')
));
```

#### Check a file existence

```php
<?php

/** @var \SixtyEightPublishers\ImageStorage\ImageStorageInterface $storage */

$pathInfo = $storage->createPathInfo('test/my-image');

if ($storage->exists($pathInfo)) {
    echo 'source image exists!';
}

if ($storage->exists($pathInfo->withModifiers(['w' => 150]))) {
    echo 'cached image with width 150 in JPEG (default) format exists!';
}

if ($storage->exists($pathInfo->withModifiers(['w' => 150])->withExt('webp'))) {
    echo 'cached image with width 150 in WEBP format exists!';
}
```

#### Deleting files

```php
<?php

use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;

/** @var \SixtyEightPublishers\ImageStorage\ImageStorageInterface $storage */

# delete all cached images only:
$storage->delete($storage->createPathInfo('test/my-image'), [
    ImagePersisterInterface::OPTION_DELETE_CACHE_ONLY => TRUE,
]);

# delete cached images and source image:
$storage->delete($storage->createPathInfo('test/my-image'));

# delete only cached image with 200px width in PNG format
$storage->delete($storage->createPathInfo('test/my-image.png')->withModifiers(['w' => 200]));
```

#### Create links to images

An original images are not accessible. If you want to access an original image you must request it with a modifier `['original' => TRUE]`.

```php
<?php

/** @var \SixtyEightPublishers\ImageStorage\ImageStorageInterface $storage */

$pathInfo = $storage->createPathInfo('test/my-image.png')
    ->withModifiers(['original' => TRUE])
    ->setVersion(time());

# /images/test/original/my-image.png?_v=1611837352
echo $storage->link($pathInfo);

# /images/test/original/my-image.webp?_v=1611837352
echo $storage->link($pathInfo->withExt('webp'));

# /images/test/ar:2x1,w:200/my-image.webp?_v=1611837352&_s={GENERATED_SIGNATURE_TOKEN}
echo $storage->link($pathInfo->withExt('webp')->withModifiers(['w' => 200, 'ar' => '2x1']));

# you can also wrap PathInfo to FileInfo object:
$fileInfo = $storage->createFileInfo($pathInfo);

# /images/test/original/my-image.png?_v=1611837352
echo $fileInfo->link();

# /images/test/original/my-image.webp?_v=1611837352
echo $fileInfo->withExt('webp')->link();

# /images/test/ar:2x1,w:200/my-image.webp?_v=1611837352&_s={GENERATED_SIGNATURE_TOKEN}
echo $fileInfo->withExt('webp')->withModifiers(['w' => 200, 'ar' => '2x1'])->link();
```

The HTML attribute `srcset` can be also generated:

```php
<?php

use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\XDescriptor;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\WDescriptor;

/** @var \SixtyEightPublishers\ImageStorage\ImageStorageInterface $storage */

$pathInfo = $storage->createPathInfo('test/my-image.png')
    ->withModifiers(['w' => 200, 'ar' => '2x1'])
    ->setVersion(time());

/*
/images/test/ar:2x1,pd:1,w:200/my-image.png?_v=1611837352&_s={TOKEN} ,
/images/test/ar:2x1,pd:2,w:200/my-image.png?_v=1611837352&_s={TOKEN} 2.0x,
/images/test/ar:2x1,pd:3,w:200/my-image.png?_v=1611837352&_s={TOKEN} 3.0x
*/
echo $storage->srcSet($pathInfo, new XDescriptor(1, 2, 3));

/*
/images/test/ar:2x1,w:200/my-image.png?_v=1611837352&_s={TOKEN} 200w,
/images/test/ar:2x1,w:400/my-image.png?_v=1611837352&_s={TOKEN} 400w,
/images/test/ar:2x1,w:600/my-image.png?_v=1611837352&_s={TOKEN} 600w,
/images/test/ar:2x1,w:800/my-image.png?_v=1611837352&_s={TOKEN} 800w
*/
echo $storage->srcSet($pathInfo, new WDescriptor(200, 400, 600, 800));

# you can also wrap PathInfo to FileInfo object:
$fileInfo = $storage->createFileInfo($pathInfo);

echo $fileInfo->srcSet(new XDescriptor(1, 2, 3));
echo $fileInfo->srcSet(new WDescriptor(200, 400, 600, 800));
```

### Usage with Latte

```neon
extensions:
    68publishers.image_storage.latte: SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\ImageStorageLatteExtension

68publishers.image_storage.latte:
    function_names:
        create_w_descriptor: w_descriptor # default
        create_x_descriptor: x_descriptor # default
        create_w_descriptor_from_range: w_descriptor_range # default
```

The extension adds these functions into the Latte:

- `w_descriptor(...)` - a shortcut for `new SixtyEightPublishers\ImageStorage\Responsive\Descriptor\XDescriptor(...)`
- `x_descriptor(...)` - a shortcut for `new SixtyEightPublishers\ImageStorage\Responsive\Descriptor\WDescriptor(...)`
- `w_descriptor_range($min, $max, $step)` - a shortcut for `SixtyEightPublishers\ImageStorage\Responsive\Descriptor\WDescriptor::fromRange($min, $max, $step)`

Basic usage:

```latte
{varType SixtyEightPublishers\ImageStorage\FileInfoInterface $fileInfo}

{* Note: method FileInfo::__toString() calls ::link() internally *}

<img src="{$fileInfo->link()}" alt="">
<img srcset="{$fileInfo->srcSet(x_descriptor(1, 2, 3))}" src="{$fileInfo}" alt="">

{* Create FileInfo from string *}
{var $fileInfo = file_info('test/my-image.png')->withModifiers(['o' => 90, 'ar' => '2x1'])}

<img srcset="{$fileInfo->srcSet(w_descriptor(400, 800, 1200))}" src="{$fileInfo}" alt="">
```

An advanced example with a tag `<picture>`:

```latte
{var $large = file_info('test/my-image.jpeg')->withModifiers([w => 1172, ar => '1x0.29'])}
{var $medium = $large->withModifiers([w => 768, ar => '1x0.59'])}

<picture>
    <source srcset="{$large->withExt('webp')->srcSet(w_descriptor_range(768, 1172 * 3, 200))}" media="(min-width: 768px)" sizes="(min-width: 1188px) calc(1188px - 2 * 0.5rem), (min-width: 992px) calc(100vw - 2 * 0.5rem), calc(100vw - 2 * 1.5rem)" type="image/webp">
    <source srcset="{$large->srcSet(w_descriptor_range(768, 1172 * 3, 200))}" media="(min-width: 768px)" sizes="(min-width: 1188px) calc(1188px - 2 * 0.5rem), (min-width: 992px) calc(100vw - 2 * 0.5rem), calc(100vw - 2 * 1.5rem)">
    <source srcset="{$medium->withExt('webp')->srcSet(w_descriptor_range(320, 768 * 3, 200))}" sizes="(min-width: 576px) calc(100vw - 2 * 1.5rem), calc(100vw - 2 * 0.5rem)" type="image/webp">
    <source srcset="{$medium->srcSet(w_descriptor_range(320, 768 * 3, 200))}" sizes="(min-width: 576px) calc(100vw - 2 * 1.5rem), calc(100vw - 2 * 0.5rem)">
    <img src="{$large}" alt="">
</picture>
```

### Symfony Console commands

The image-storage extends a command `file-storage:clean` with an option `cache-only` so the command now looks like this:

```bash
$ bin/console file-storage:clean [<storage>] [--namespace <value>] [--cache-only]
```

## Supported image formats and modifiers

### Image formats

- JPEG - `.jpeg` or `.jpg`
- Progressive JPEG - `.pjpg`
- PNG - `.png`
- GIF - `.gif`
- WEBP - `.webp`

### Modifiers

| Name | Shortcut | Type | Note |
| --- | --- | --- | --- |
| Original | original | - | A modifier without a value, use it if you want to return the original image |
| Height | h | Integer | Can be restricted by parameter `AllowedResolutions` |
| Width | w | Integer | Can be restricted by parameter `AllowedResolutions` |
| Pixel density | pd | Integer\|Float | Can be restricted by parameter `AllowedPixelDensity` |
| Aspect ratio | ar | String | Required format is `{Int\|Float}x{Int\|Float}` and a height or a width (not both) must be also defined. For example `w:200,ar:1x2` is an equivalent of `w:200,h:400` |
| Fit | f | String | See [supported fits](#supported-fits) for the list of supported values |
| Orientation | o | Integer\|String | Allowed values are `auto, 0, 90, -90, 180, -180, 270, -270` |
| Quality | q | Integer | Can be restricted by parameter `AllowedQualities` |

### Supported fits

- `contain` - Preserving aspect ratio, resize the image to be as large as possible while ensuring its dimensions are less than or equal to both those specified.
- `stretch` - Ignore the aspect ratio of the input and stretch to both provided dimensions.
- `fill` - Preserving aspect ratio, contain within both provided dimensions using "letterboxing" where necessary.
- `crop-*` - Preserving aspect ratio, ensure the image covers both provided dimensions by cropping to fit.
    - `crop-center`
    - `crop-left`
    - `crop-right`
    - `crop-top`
    - `crop-top-left`
    - `crop-top-right`
    - `crop-bottom`
    - `crop-bottom-left`
    - `crop-bottom-right`

## Image server

### Local image server

The default image server for each storage is `local`. That means your application will handle requests and will generate, store and serve modified images.
Everything is prepared but the application must provide some endpoint. Here is an example of how to do it:

```php
<?php

use SixtyEightPublishers\ImageStorage\Bridge\Nette\Presenter\AbstractImageServerPresenter;

final class ImagePresenter extends AbstractImageServerPresenter
{
    # You can define a storage name through this property. The default storage is used if the property is not overridden
    protected $storageName = 'local';
}
```

In the application's router:

```php
/** @var Nette\Application\Routers\RouteList $router */

$router->addRoute('images/<path .+>', 'Image:default');
```

Then you must modify the configuration of a web server. For example, if the webserver is Apache then modify a file `.htaccess` that is located in your www directory.

```apacheconf
# locale images
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(images\/)(.+) index.php [L]
```

The Application will be called only if a static file has not yet been generated. Otherwise, the server will serve the static file.


### External image server: an integration with AWS S3 and image-storage-lambda

The image storage can be integrated with the Amazon S3 object storage and the package [68publishers/image-storage-lambda](https://github.com/68publishers/image-storage-lambda). So your image storage can be completely serverless!
Of course you can deploy the `image-storage-lambda` application manually and also synchronize options from the `image-storage` with the `image-storage-lambda` manually.

At least you can follow these simple steps for a partial integration:

1) Create a deployment bucket on the S3

When you deploy the AWS SAM application in guide mode (`sam deploy --guided`) the deployment bucket will be created automatically. But the application will be built in a non-guided mode so we must create the bucket manually.
If you don't know how to create an S3 bucket please follow the [Amazon documentation](https://docs.aws.amazon.com/AmazonS3/latest/gsg/CreatingABucket.html). We recommend to enable versioning on this bucket.

2) Required packages `league/flysystem-aws-s3-v3` (the S3 adapter for Flysystem) and `yosymfony/toml` (suggested by this package) in your application

```bash
$ composer require league/flysystem-aws-s3-v3 yosymfony/toml
```

3) Configure the image storage with the S3 filesystem (an example with a minimal configuration):

```neon
services:
    s3_client:
        class: Aws\S3\S3Client([... your S3 config ...])
        autowired: no

68publishers.file_storage:
    storages:
        s3_images:
            config:
                # configure what you want but omit the `host` option for now
            filesystem:
                adapter: League\Flysystem\AwsS3V3\AwsS3V3Adapter(@s3_client, my-awesome-cache-bucket) # a bucket doesn't exists at this point
            # if you have your own no-images:
            assets:
                %assetsDir%/noimage: noimage

68publishers.image_storage:
    storages:
        s3_images:
            source_filesystem:
                adapter: League\Flysystem\AwsS3V3\AwsS3V3Adapter(@s3_client, my-awesome-source-bucket) # a bucket doesn't exists at this point
            server: external
            # if you have your own no-images:
            no_image:
                default: noimage/default.png
                user: noimage/user.png
            no_image_patterns:
                user: '^user_avatar\/'
```

4) Register and configure a compiler extension `ImageStorageLambdaExtension`

```neon
extensions:
    68publishers.image_storage.lambda: SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\ImageStorageLambdaExtension

68publishers.image_storage.lambda:
    output_dir: %appDir%/config/image-storage-lambda # this is default
    stacks:
        s3_images:
            stack_name: my-awesome-image-storage
            s3_bucket: {NAME OF YOUR DEPLOYMENT BUCKET FROM THE STEP 1}
            region: eu-central-1

            # optional settings:
            version: 2.0 # default is 1.0
            s3_prefix: custom-prefix # a stack_name is used by default
            confirm_changeset: yes # default false, must be changeset manually confirmed during deploy?
            capabilities: CAPABILITY_IAM # default, CAPABILITY_IAM or CAPABILITY_NAMED_IAM only

            # optional, automatically detected from AwsS3V3Adapter by default
            source_bucket_name: source-bucket-name
            cache_bucket_name: cache-bucket-name
```

5) Generate configuration for the `image-storage-lambda`

```bash
$ php bin/console image-storage:lambda:dump-config
```

The configuration file will be placed by default in a directory `app/config/image-storage-lambda/my-awesome-image-storage/samconfig.toml`. Keep this file versioned in the Git.

6) Download `image-storage-lambda`, build and deploy!

Firstly setup your local environment by requirements defined [here](https://github.com/68publishers/image-storage-lambda#requirements). Then download the package outside your project.

```bash
$ git clone https://github.com/68publishers/image-storage-lambda.git image-storage-lambda
$ cd ./image-storage-lambda
```

Unfortunately SAM CLI doesn't allow you to define a path to your `samconfig.toml` file (related issue https://github.com/awslabs/aws-sam-cli/issues/1615) at this moment.
So you must copy the config to the root of the `image-storage-lambda` application.
And then you can build and deploy the application!

```sh
$ cp ../my-project/app/config/image-storage-lambda/my-awesome-image-storage/samconfig.toml samconfig.toml
$ sam build
$ sam deploy
```

7) Set the CloudFront URL as a host in the image storage config

The URL of your CloudFront distribution is listed in Outputs after a successful deployment. More information are [here](https://github.com/68publishers/image-storage-lambda#what-is-the-url-of-my-api).

```neon
# ...
68publishers.image_storage:
    storages:
        s3_images:
            config:
                host: {CLOUDFRONT URL}
# ...
```

## Contributing

Before committing any changes, don't forget to run

```bash
vendor/bin/php-cs-fixer fix --config=.php_cs.dist -v --dry-run
```

and

```bash
vendor/bin/tester ./tests
```
