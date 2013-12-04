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

=====

# Caveats and The Future...?

The following caveats should apply:

* This is not production ready or highly-scalable
* Timestamps rounded to purchased time should be added to cookies
* The transaction checks should be precision-based (currently gets most recent)

In the future we would like to improve this example by:

* Fixing the caveats
* Offering additional currencies payment options
* Using local-storage first then cookies as back-up
* AJAX polling for complete transaction and auto-reloading

Help to support the [Block-Chain Embassy](http://bce.asia) by donating to the following address - [1MBQ551Lws1iKvU1CK3Ly1tAREYLQT1g3g](https://blockchain.info/address/1MBQ551Lws1iKvU1CK3Ly1tAREYLQT1g3g)