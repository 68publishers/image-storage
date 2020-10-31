# Image Storage

Image storage for Nette Framework generating images on-the-fly based on [thephpleague/flysystem](https://github.com/thephpleague/flysystem) and [intervention/image](https://github.com/Intervention/image).

## Installation

The best way to install 68publishers/image-storage is using Composer:

```bash
composer require 68publishers/image-storage
```

then you can register extension into DIC:

```yaml
extensions:
    image_storage: SixtyEightPublishers\ImageStorage\DI\ImageStorageExtension
```

## Configuration

```yaml
image_storage:
    config:
        # a common configuration variables, look on the default values into Config class
        base_path: /images
        host: %images.host_url% # or NULL
        version_parameter_name: v
        signature_parameter_name: s
        signature_algorithm: sha256 # default
        signature_key: my-arbitrary-private-key
        allowed_pixel_density: [ 1, 2, 3 ]
        allowed_resolutions: [ 50x50, 200x200, 300x300, 200x, x200 ]
        allowed_qualities: [ 50, 80, 100 ]
        encode_quality: 90
        cache_max_age: 31536000
        modifier_separator: ','
        modifier_assigner: ':'

    driver: gd # default, 'gd' or 'imagick'
    
    bridge:
        # if you want to register a Latte macros, a default is yes
        latte_macros: yes
        # if you want to register a Doctrine type, a default is no
        doctrine_type: yes

    # You must define almost one storage
    storages:
        local:
            source:
                adapter: League\Flysystem\Adapter\Local(%wwwDir%/images)
                config: # Flysytem config, this is a default value defined by an extension:
                    visibility: ::constant(League\Flysystem\AdapterInterface::VISIBILITY_PRIVATE)

            cache:
                adapter: League\Flysystem\Adapter\Local(%appDir%/../private/images)
                config: # Flysytem config, this is a default value defined by an extension:
                    visibility: ::constant(League\Flysystem\AdapterInterface::VISIBILITY_PUBLIC)

            server: local # "local" or "external", a default is local
            
            # predefined presets
            presets:
                xs: { w: 50, h: 50 }
                sm: { w: 90, h: 90 }
                md: { w: 300, h: 300 }
                lg: { w: 600, h: 600 }
                xl: { w: 1000, h: 709 }
                foo: { w: 20, h: 20, o: 90 }
                
            # default assets, images are synchronized via a console command
            assets:
                %assetsDir%/images/noimage: noimage # copy directory
                %assetsDir%/images/foo/bar.png: foo/bar.png # copy single file
                
            # no-image paths, use a key `default` for a default image
            no_image:
                default: noimage/noapp.png
                user: noimage/nouser.png
                
            # define patters for a no-image resolving
            no_image_patterns:
                user: '^user\/' # all images that started with a namespace `user/` will have a no-image `user`
                
            # if you want to override a default Modifiers, Validators or Applicator, define it here. Otherwise don't define these keys
            modifiers: []
            validators: []
            applicators: []
```

If you're using `Local` adapter you must modify `.htaccess` that is located in your www directory. For example:

```apacheconf
# locale images
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(images\/)(.+) index.php [L]
```

The Application will be called only if a static file has not yet been generated. Otherwise the server will serve the static file.

## Usage

@todo

### Latte Macros

#### Macro "src"

Arguments:

| # | Type | Required | Default | Note |
| ----- | ----- | ----- | ----- | ----- |
| 1 | `string`\|`SixtyEightPublishers\ImageStorage\ImageInfo` | yes | - | a path to the requested image or an ImageInfo object |
| 2 | `string`\|`array` | yes | - | an array with modifiers or preset's name (defined in configuration) |
| 3 | `string`\|`null` | no | null | a name of the storage. A default storage is used if argument isn't provided |

```latte
{src 'namespace/file.png', [ w => 300, h => 600 ]}
{src $info, [ w => 300, h => 600 ], s3_storage}
{src $info, my_preset}

<img n:src="'namespace/file.png', [ w => 300, h => 600 ]" alt="">
<img n:src="$info, [ w => 300, h => 600 ], s3_storage" alt="">
<img n:src="$info->ext(webp), my_preset" alt="">
```

#### Macro "srcset"

Arguments:

| # | Type | Required | Default | Note |
| ----- | ----- | ----- | ----- | ----- |
| 1 | `string`\|`SixtyEightPublishers\ImageStorage\ImageInfo` | yes | - | a path to the requested image or an ImageInfo object |
| 2 | `SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor` | yes | - | a helper functions `w_descriptor()`, `x_descriptor()` and `w_descriptor_range()` are available |
| 3 | `string`\|`array`\|`null` | no | null | an array with modifiers or preset's name (defined in configuration) or null |
| 4 | `string`\|`null` | no | null | a name of the storage. A default storage is used if argument isn't provided |

```latte
{srcset 'namespace/file.png', x_descriptor(1, 2, 3)}
{srcset $info, x_descriptor(1, 2, 3), null, s3_storage}
{srcset $info, w_descriptor(300, 500, 700, 900, 1100), [ ar => '2x1' ]}

<img n:srcset="'namespace/file.png', x_descriptor(1, 2, 3)" alt="">
<img n:srcset="$info, x_descriptor(1, 2, 3), null s3_storage" alt="">
<img n:srcset="$info->ext(jpeg), w_descriptor(300, 500, 700, 900, 1100), [ ar => '2x1' ]" alt="">

<img n:srcset="$info, w_descriptor(300, 500, 700, 900, 1100), [ ar => '2x1' ]" n:src="$info, [w => 300, h => 150]" alt="">
```

If the HTML attribute `src` or the Latte Macro `n:src` is not defined then the `src` attribute is generated automatically by `n:srcset` macro with the lowest size.

#### Macro "picture"

Picture arguments:

| # | Type | Required | Default | Note |
| ----- | ----- | ----- | ----- | ----- |
| 1 | `string`\|`SixtyEightPublishers\ImageStorage\ImageInfo` | yes | - | a path to the requested image or an ImageInfo object |
| 2 | `string`\|`null` | no | null | a name of the storage. A default storage is used if argument isn't provided |

Src arguments:

| # | Type | Required | Default | Note |
| ----- | ----- | ----- | ----- | ----- |
| 1 | `string`\|`array` | yes | - | an array with modifiers or preset's name (defined in configuration) |

Srcset arguments:

| # | Type | Required | Default | Note |
| ----- | ----- | ----- | ----- | ----- |
| 1 | `SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor` | yes | - | a helper functions `w_descriptor()`, `x_descriptor()` and `w_descriptor_range()` are available |
| 2 | `string`\|`array`\|`null` | no | null | an array with modifiers or preset's name (defined in configuration) or null |

```latte
<picture n:picture="'namespace/file.png'">
    <source n:srcset="w_descriptor(320, 640, 1024)" media="(min-width: 36em)" sizes="33.3vw" type="image/webp">
    <source n:srcset="w_descriptor(320, 640, 1024)" media="(min-width: 36em)" sizes="33.3vw">
    <source n:srcset="x_descriptor(1,2), [ w => 300 ]" type="image/webp">
    <source n:srcset="x_descriptor(1,2), [ w => 300 ]">
    <img n:src="[w => 300]" alt="">
</picture>
```

The format of the requested image can be changed via the HTML attribute `type` and vice versa the `type` attribute will be generated from an ImageInfo's file extension if the attribute is missing.


## Integration with AWS S3 and image-storage-lambda

The image storage can be integrated with the Amazon S3 object storage and the package [68publishers/image-storage-lambda](https://github.com/68publishers/image-storage-lambda). So your image storage can be completely serverless!
Of course you can deploy the `image-storage-lambda` application manually and also synchronize options from the `image-storage` with the `image-storage-lambda` manually.

At least you can follow these simple steps for a partial integration:

1) Create a deployment bucket on the S3

When you deploy the AWS SAM application in guide mode (`sam deploy --guided`) the deployment bucket will be created automatically. But the application will be built in a non-guided mode so we must create the bucket manually.
If you don't know how to create an S3 bucket please follow the [Amazon documentation](https://docs.aws.amazon.com/AmazonS3/latest/gsg/CreatingABucket.html). We recommend to enable versioning on this bucket.

2) Required packages `league/flysystem-aws-s3-v3` (the S3 adapter for Flysystem) and `yosymfony/toml` (suggested by this package) in your application

3) Configure the image storage with the S3 filesystem (an example with a minimal configuration):

```yaml
extensions:
    68publishers.imageStorage: SixtyEightPublishers\ImageStorage\DI\ImageStorageExtension

services:
    s3Client:
        class: Aws\S3\S3Client([... your S3 config ...])
        autowired: no

68publishers.imageStorage:
    config:
        # configure what you want but omit the `host` option for now

    storages:
        s3:
            source:
                adapter: League\Flysystem\AwsS3v3\AwsS3Adapter(@s3Client, my-awesome-source-bucket) # a bucket doesn't exists at this point
            cache:
                adapter: League\Flysystem\AwsS3v3\AwsS3Adapter(@s3Client, my-awesome-cache-bucket) # a bucket doesn't exists at this point
            server: external
            # if you have your own no-images:
            assets:
                %assetsDir%/images/noimage: noimage
            no_image:
                default: noimage/default.png
                user: noimage/user.png
            no_image_patterns:
                user: '^user\/'
```

4) Register and configure the `ImageStorageLambda` extension

```yaml
extensions:
    68publishers.imageStorageLambda: SixtyEightPublishers\ImageStorage\DI\ImageStorageLambdaExtension

68publishers.imageStorageLambda:
    output_dir: %appDir%/config/image-storage-lambda # this is default
    stacks:
        s3:
            stack_name: my-awesome-image-storage
            s3_bucket: {NAME OF YOUR DEPLOYMENT BUCKET FROM THE STEP 1}
            # optional settings:
            version: 2.0 # default is 1.0
            s3_prefix: custom-prefix # a stack_name is used by default
            region: e-central-1 # a region from your S3 client is used by default
            confirm_changeset: yes # default false, must be changeset manually confirmed during deploy?
            capabilities: CAPABILITY_IAM # default, CAPABILITY_IAM or CAPABILITY_NAMED_IAM only
```

5) Generate configuration for the `image-storage-lambda`

```sh
$ php bin/console image-storage:lambda:dump-config
```

The configuration file will be placed by default in a directory `app/config/image-storage-lambda/my-awesome-image-storage/samconfig.toml`. Keep this file versioned in the Git.

6) Download `image-storage-lambda`, build and deploy!

Firstly setup your local environment by requirements defined [here](https://github.com/68publishers/image-storage-lambda#requirements). Then download the package outside your project.

```sh
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

```yaml
# ...
68publishers.imageStorage:
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
