#Users demodata
REPLACE INTO `oxuser` SET
    OXID = 'amazonpayuser',
    OXACTIVE = 1,
    OXRIGHTS = 'user',
    OXSHOPID = 1,
    OXUSERNAME = 'amazonpayuser@oxid-esales.dev',
    OXPASSWORD = '$2y$10$tJd1YkFr2y4kUmojqa6NPuHrcMzZmxc9mh4OWQcLONfHg4WXzbtlu',
    OXPASSSALT = '',
    OXFNAME = 'TestUserName',
    OXLNAME = 'TestUserSurname',
    OXSTREET = 'Musterstr.šÄßüл',
    OXSTREETNR = '12',
    OXCITY = 'City',
    OXZIP = '12345',
    OXCOUNTRYID = 'a7c40f631fc920687.20179984',
    OXBIRTHDATE = '1985-02-05 14:42:42',
    OXCREATE = '2021-02-05 14:42:42',
    OXREGISTER = '2021-02-05 14:42:42';

REPLACE INTO `oxobject2payment` SET
    OXID = 'd555989e19c98a1aa5ac1a0efced9f34',
    OXPAYMENTID = 'oxidamazon',
    OXOBJECTID = 'oxidstandard',
    OXTYPE = 'oxdelset',
    OXTIMESTAMP = '2021-02-05 14:42:42'