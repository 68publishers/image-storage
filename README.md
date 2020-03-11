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
    env:
        # "env" variables, look on default values into Env class
        BASE_PATH: /images
        HOST: %images.host_url% # or NULL
        VERSION_PARAMETER_NAME: v
        SIGNATURE_PARAMETER_NAME: s
        ALLOWED_PIXEL_DENSITY: [ 1, 2, 3 ]
        ALLOWED_RESOLUTIONS: [ 50x50, 200x200, 300x300, 200x, x200 ]
        ALLOWED_QUALITIES: [ 50, 80, 100 ]
        ENCODE_QUALITY: 90
        MODIFIER_SEPARATOR: ','
        MODIFIER_ASSIGNER: ':'

    driver: gd # default, 'gd' or 'imagick'
    
    bridge:
        # if you want to register Latte macros, default yes
    	latte_macros: yes
        # if you want to register Doctrine type, default no
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

            server: local # "local" or "external", default is local

            signature: my-arbitrary-private-key # a default value is NULL. If the value is a string then its passed as a privateKey into a DefaultSignatureStrategy class. 
            # Eventually you can pass custom strategy e.g. My\Awesome\SignatureStrategy(foo, bar)
            
            # predefined presets
            presets:
                xs: { w: 50, h: 50 }
                sm: { w: 90, h: 90 }
                md: { w: 300, h: 300 }
                lg: { w: 600, h: 600 }
                xl: { w: 1000, h: 709 }
                foo: { w: 20, h: 20, pf: 1, o: 90 }
                
            # default assets, images are synchronized via console command
            assets:
                %assetsDir%/images/noimage: noimage # copy directory
                %assetsDir%/images/foo/bar.png: foo/bar.png # copy single file
                
            # no-image paths, use key `default` for default image
            no_image:
                default: noimage/noapp.png
                user: noimage/nouser.png
                
            # define patters for no-image resolving
            no_image_rules:
                user: '^user\/' # all images that started with namespace `user/` will have no-image `user`
                
            # if you want to override default Modifiers, Validators or Applicator, defined it here. Otherwise don't define these keys
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

The Application will be called only if static file has not yet been generated. Otherwise server will serve static file.

## Usage

@todo

## Contributing

Before committing any changes, don't forget to run

```bash
vendor/bin/php-cs-fixer fix --config=.php_cs.dist -v --dry-run
```

and

```bash
vendor/bin/tester ./tests
```
