<?php

namespace OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Admin\Products;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Codeception\Page\Details\ProductDetails;
use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\AcceptanceTester;

final class AmazonPayExcludeCest extends BaseCest
{
    protected array $excluded;
    protected string $amazonExcludeProductConfig = '[name="editval[oxarticles__osc_amazon_exclude]"]:nth-child(2)';
    protected string $amazonExcludeCategoryConfig = '[name="editval[oxcategories__osc_amazon_exclude]"]:nth-child(2)';
    public string $searchForm = '#search';


    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayExcludeTest
     * @throws \Exception
     */
    public function checkAmazonPayExcludeProductWorks(AcceptanceTester $I): void
    {

        $this->excluded = Fixtures::get('amazonExclude');
        $I->wantToTest('AmazonPay Exclude Option for products are working');
        $this->_loginAdmin();
        $this->_openAdminAmazonPayConfig();
        $I->scrollTo('#useExclusion');
        $I->checkOption('#useExclusion');
        $I->submitForm('.amazonpay-config form', []);
        $I->waitForDocumentReadyState();
        $error = $this->_grabTextFromElementWhenPresent('.alert-danger', $I);
        if ($error) {
            $I->fail('Error on saving amazon module config: ' . $error);
        }
        $I->waitForElement('.alert-success', 60);

        $I->selectNavigationFrame();
        $I->click(Translator::translate('mxmanageprod'));
        $I->click(Translator::translate('mxarticles'));
        $products = new Products($I);
        $products->find('where[oxarticles][oxartnum]', $this->excluded['excludedProduct']['artId']);
        #$I->wait(5);
        try {
            $I->waitForElement($this->amazonExcludeProductConfig, 60);
        } catch (\Exception $e) {
            $I->fail('Can\'t find amazon exclude option in product config');
        }
        $I->click($this->amazonExcludeProductConfig);
        $I->click('#oLockButton');
        $I->wait(5);


        $I->openShop();
        $I->waitForDocumentReadyState();
        $I->waitForPageLoad();
        $detailPage = new ProductDetails($I);
        $detailUrl = $detailPage->route($this->excluded['excludedProduct']['id']);
        $I->amOnPage($detailUrl);
        $I->waitForDocumentReadyState();
        try {
            $I->waitForElement('#AmazonPayButtonProductMain', 5);
            $I->fail('Amazon Express button is shown, but the article is excluded from Amazon Pay');
        } catch (\Exception $e) {
            // don't fail if the element does not show up
        }

        $detailPage = new ProductDetails($I);
        $detailUrl = $detailPage->route($this->excluded['notExcludedProduct']['id']);
        $I->amOnPage($detailUrl);
        $I->waitForDocumentReadyState();
        try {
            $I->waitForElement('#AmazonPayButtonProductMain', 60);
        } catch (\Exception $e) {
            $I->fail('Amazon Express but is absent, but should be shown');
        }
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayExcludeTest
     * @group b
     * @throws \Exception
     */
    public function checkAmazonPayExcludeCategoryWorks(AcceptanceTester $I): void
    {
        $this->excluded = Fixtures::get('amazonExclude');
        $I->wantToTest('AmazonPay Exclude Option for categories is  working');
        $this->_loginAdmin();
        $this->_openAdminAmazonPayConfig();
        $I->scrollTo('#useExclusion');
        $I->checkOption('#useExclusion');
        $I->submitForm('.amazonpay-config form', []);
        $I->waitForDocumentReadyState();
        $error = $this->_grabTextFromElementWhenPresent('.alert-danger', $I);
        if ($error) {
            $I->fail('Error on saving amazon module config: ' . $error);
        }
        $I->waitForElement('.alert-success', 60);

        // open category in admin
        $I->selectNavigationFrame();
        $I->click(Translator::translate('mxmanageprod'));
        $I->click(Translator::translate('mxcategories'));
        $I->selectListFrame();
        $I->fillField('where[oxcategories][oxsort]', $this->excluded['categoryExcluded']['sortNum']);
        $I->submitForm($this->searchForm, []);
        $I->selectListFrame();
        $I->click($this->excluded['categoryExcluded']['sortNum']);
        $I->selectListFrame();
        $I->selectEditFrame();


        // activate the exclusion for the category
        try {
            $I->waitForElement($this->amazonExcludeCategoryConfig, 60);
        } catch (\Exception $e) {
            $I->fail('Can\'t find amazon exclude option in category config');
        }
        $I->click($this->amazonExcludeCategoryConfig);
        $I->click('[name="save"]');
        $I->wait(5);

        // check if button is not shown in product details page for an article in an excluded category
        $I->openShop();
        $I->waitForDocumentReadyState();
        $I->waitForPageLoad();
        $detailPage = new ProductDetails($I);
        $detailUrl = $detailPage->route($this->excluded['productInExcludedCategory']['id']);
        $I->amOnPage($detailUrl);
        $I->waitForDocumentReadyState();
        try {
            $I->waitForElement('#AmazonPayButtonProductMain', 5);
            $I->fail('Amazon Express button is shown, but the article in a category which is excluded from Amazon Pay');
        } catch (\Exception $e) {
            // don't fail if the element does not show up
        }

        // check if button is shown in product details page of a product which is not in the excluded category
        $detailPage = new ProductDetails($I);
        $detailUrl = $detailPage->route($this->excluded['notExcludedProduct']['id']);
        $I->amOnPage($detailUrl);
        $I->waitForDocumentReadyState();
        try {
            $I->waitForElement('#AmazonPayButtonProductMain', 60);
        } catch (\Exception $e) {
            $I->fail('Amazon Express is absent, but should be shown');
        }
    }
}
