[![](https://img.shields.io/packagist/v/wangaz/contao-isotope-wirecard.svg)](https://packagist.org/packages/wangaz/contao-isotope-wirecard)
[![](https://img.shields.io/packagist/dt/wangaz/contao-isotope-wirecard.svg)](https://packagist.org/packages/wangaz/contao-isotope-wirecard)

Wirecard Payment Module for Isotope for Contao
======================

This module enables you to use [Wirecard](http://www.wirecard.com) as a payment module for [Isotope eCommerce](https://isotopeecommerce.org) for [Contao CMS](https://contao.org). The implementation is based on the [Hosted Payment Page](https://document-center.wirecard.com/display/PTD/Hosted+Payment+Page) variant of the _Wirecard Payment Page v2_.

## Configuration

In the payment module, you have to configure the following setting:

* Username
* Password
* Merchant ID
* Secret

Additionally, you can choose a payment method. When a payment method is chosen, the customer will be redircted directly to that payment method. Otherwise the custome will see an overview page by Wirecard, where they can choose the payment method. If the Wirecard credentials are only applicable for one payment method, no overview page will be shown by Wirecard.

There is also an option to enable the test API host, where you can use any of the test credentials in the Wirecard documentation (e.g. [here](https://document-center.wirecard.com/display/PTD/HPP+Integration#HPPIntegration-SetupandTestCredentials)).

![](https://raw.githubusercontent.com/wangaz/contao-isotope-wirecard/contao4/screenshot.png)

## License

This work is licensed under a [Creative Commons Attribution-ShareAlike 4.0 International License](http://creativecommons.org/licenses/by-sa/4.0/).

## Attributions

__Thanks a lot to [Christian Regauer (Regauer | Feinste Werbelösungen)](http://www.regauer.at), who made the development of this module possible.__

Implementation of the new Wirecard Payment Page solution done by [inspiredminds](https://github.com/inspiredminds).
