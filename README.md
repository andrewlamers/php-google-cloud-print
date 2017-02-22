## Php Google Cloud Print
PHP Interface to the google cloud print API.

#### Installation
`composer require andrewlamers/php-google-cloud-print`

### Google Service Account
You can use a google service account with this api. To give the service account access to your printers, share the printer with the service account email.
The api will try to accept invites automatically.

### Laravel Setup
Included service provider and facade for Laravel 5.


    'providers' => [
        \Andrewlamers\PhpGoogleCloudPrint\ServiceProvider::class
    ]

    'aliases' => [
        'CloudPrint' => Andrewlamers\PhpGoogleCloudPrint\Facade::class
    ]
    
### Example Usage

    $response = CloudPrint::html('<html><body>My html content</body></html>')
        ->printer('myprinterid')
        ->send();
        
        
### Available Options
    CloudPrint::html('mystring')
        ->printer($printerid)
        ->content($stringContent)
        ->title('my job title')
        ->tag('my job tag')
        ->