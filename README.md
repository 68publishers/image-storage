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
                foo: { w: 20, h: 20, pf: 1, o: 90 }
                
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
