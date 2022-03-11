# Notifications

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

## Installation

Via Composer

``` bash
$ composer require carropublic/notifications
```

Run the following vendor publish to publish Twillo config.

```bash
php artisan vendor:publish --provider="CarroPublic\Notifications\NotificationServiceProvider"
```

## Usage

### LaravelNotification

The following is the example usage of the package with Laravel's Notification.

#### Create Notification Class

```
class ExampleNotification extends Notification
{
    // Which channel this notification should be sent to
    public function via($notifiable)
    {
        return [ SMSChannel::class, WhatsAppChannel::class ];
    }
    
    // Notification payload (content) will be sent
    public function toSMS($notifiable)
    {
        return new LaravelTwilioMessage("Message Content");
    }
    
    // Notification payload (content) will be sent
    public function toWhatsApp($notifiable)
    {
        return new LaravelTwilioMessage("Message Content");
    }
}
```

#### Create Notifiable Class

```
class Contact extends Model {

    use Notifiable;
    
    // Phone number to receive
    public function routeNotificationForSms()
    {
        return $this->phone;
    }
    
    // Phone number to receive
    public function routeNotificationForWhatsapp()
    {
        return $this->phone;
    }
}
```

##### Sending Notification from Notifiable Instance

```
$contact->notify(new ExampleNotification());
```

##### Sending Notification from Anonymous Notifiable Instance

```
Notification::route('sms')->notify(new ExampleNotification());
```

## Sandbox Mode

#### How to enable SandBox Mode

1. Register Closure to return if testing is enabled `\CarroPublic\Notifications\Senders\Sender::registerSandboxValidator`

Example:

```
Sender::registerSandboxValidator(function () {
    return !is_production();
});
```

2. Otherwise, use`NOTIFICATION_SANDBOX_ENABLE` to determine if running in sandbox mode. Default `false`

**⛔️ Notes: If the closure is registered, the env will be ignored**

#### How to bypass sandbox validator

- Register Closure to return if sandbox is enabled `\CarroPublic\Notifications\Senders\Sender::registerValidForSandbox`

$to: the recipient the message will be sent to
$type: the sender class
 - LineSender
 - TelerivetSender
 - TwilioSender
 - MailChannel (Since there is not MailSender, the MailChannel will be used instead)
 
```
LaravelTwilioSender::registerValidPhoneForSandbox(function ($to, $type) {
    switch ($type) {
        case LineSender::class:
            return true;
        case TwilioSender::class:
            return false;
    }
}
```

## Change log

Please see the [changelog](/CHANGELOG.md) for more information on what has changed recently.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [Carro][link-author]
- [All Contributors][link-contributors]

## License

Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/carropublic/notifications.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/carropublic/notifications.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/carropublic/notifications
[link-downloads]: https://packagist.org/packages/carropublic/notifications
[link-author]: https://github.com/carro-public
[link-contributors]: ../../contributors]
