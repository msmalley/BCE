# BC Embassy PHP Login Example

[BCE.asia](http://bce.asia) is a non-profit dedicated to educating Asian communities on block-chain technologies.

This freely available example uses PHP and the block-chain as a way to authenticate login without the need for a database and for the length of that login to be based on the amount of Bitcoin the user directly sends.

=====

This example will not work unless hosted within an environment that also has the [BitcoinQT](http://bitcoin.org/en/download) running.

You will also need to rename config-sample.ini to just config.ini and update the Bitcoin server specific details.

From here, you can also control how much BTC it costs for 24 hours of access.

=====

# Screenshots

If not logged-in and Bitcoin server is online and config.ini is set correctly, you should see something like:

![](../screenshots/php-login/denied.jpg?raw=true)

Manually refresh the page once payment has been made and if all goes well, you should see this:

![](../screenshots/php-login/granted.jpg?raw=true)