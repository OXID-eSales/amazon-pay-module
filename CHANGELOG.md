# Change Log for OXID eSales Amazon Pay

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [1.1.2] - Backward-Compatibility for OXID 6.1

* Add Backward-Compatibility for OXID 6.1
* Add Secure use of OrderController::execute
* Fallback InvoiceAddress (With the Amazon button (without OXID login) we use the billing address
  from Amazon. However, if this does not correspond to the shop countries, we fall back on the
  Amazon delivery address as the billing address, since the delivery addresses have already been
  restricted by country beforehand)

## [1.1.1] - Maintenance Release

* change handling of required fields

## [1.1.0] - First Release for OXID 6.2

* change the module-id to oxps_amazonpay
* configuration-handling OXID6.2 compatible

## [Undecided] - unreleased

## [1.0.0] - First Release for OXID 6.1
