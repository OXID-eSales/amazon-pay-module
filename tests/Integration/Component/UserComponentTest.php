<?php

/**
 * This file is part of OXID eSales AmazonPay module.
 *
 * OXID eSales AmazonPay module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales AmazonPay module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales AmazonPay module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2020
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\AmazonPay\Tests\Integration\Component;

use OxidEsales\Eshop\Application\Component\UserComponent;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;

/**
 * Sign-in via the Amazon email address (module setting amazonPayLoginByEMail).
 */
class UserComponentTest extends UnitTestCase
{
    private const EMAIL = 'amazonlogintest@oxid-esales.com';

    protected function setUp(): void
    {
        parent::setUp();

        $this->addTableForCleanup('oxuser');
        $this->setConfigParam('amazonPayLoginByEMail', Constants::LOGIN_BY_EMAIL_OFF);
    }

    protected function tearDown(): void
    {
        $component = oxNew(UserComponent::class);
        $component->setUser(null);
        Registry::getSession()->deleteVariable('usr');
        unset($_POST['stoken']);

        parent::tearDown();
    }

    public function testSignInIsSkippedWhenFeatureIsDisabled(): void
    {
        $this->createShopUser('');
        $this->provideSessionChallenge();

        $this->assertFalse($this->loginAmazonCustomer());
        $this->assertNull(Registry::getSession()->getVariable('usr'));
    }

    public function testGuestAccountIsSignedInInGuestOnlyMode(): void
    {
        $userId = $this->createShopUser('');
        $this->setConfigParam('amazonPayLoginByEMail', Constants::LOGIN_BY_EMAIL_GUEST_ONLY);
        $this->provideSessionChallenge();

        $this->assertTrue($this->loginAmazonCustomer());
        $this->assertSame($userId, Registry::getSession()->getVariable('usr'));
    }

    public function testPasswordProtectedAccountIsNotSignedInInGuestOnlyMode(): void
    {
        $this->createShopUser('someSecret123');
        $this->setConfigParam('amazonPayLoginByEMail', Constants::LOGIN_BY_EMAIL_GUEST_ONLY);
        $this->provideSessionChallenge();

        $this->assertFalse($this->loginAmazonCustomer());
        $this->assertNull(Registry::getSession()->getVariable('usr'));
    }

    public function testPasswordProtectedAccountIsSignedInInAllAccountsMode(): void
    {
        $userId = $this->createShopUser('someSecret123');
        $this->setConfigParam('amazonPayLoginByEMail', Constants::LOGIN_BY_EMAIL_ALL);
        $this->provideSessionChallenge();

        $this->assertTrue($this->loginAmazonCustomer());
        $this->assertSame($userId, Registry::getSession()->getVariable('usr'));
    }

    public function testAdminAccountIsNeverSignedIn(): void
    {
        $this->createShopUser('someSecret123', 'malladmin');
        $this->setConfigParam('amazonPayLoginByEMail', Constants::LOGIN_BY_EMAIL_ALL);
        $this->provideSessionChallenge();

        $this->assertFalse($this->loginAmazonCustomer());
        $this->assertNull(Registry::getSession()->getVariable('usr'));
    }

    public function testInactiveAccountIsNotSignedIn(): void
    {
        $this->createShopUser('', 'user', 0);
        $this->setConfigParam('amazonPayLoginByEMail', Constants::LOGIN_BY_EMAIL_ALL);
        $this->provideSessionChallenge();

        $this->assertFalse($this->loginAmazonCustomer());
        $this->assertNull(Registry::getSession()->getVariable('usr'));
    }

    public function testSignInRequiresTheSessionChallenge(): void
    {
        $this->createShopUser('');
        $this->setConfigParam('amazonPayLoginByEMail', Constants::LOGIN_BY_EMAIL_ALL);

        $this->assertFalse($this->loginAmazonCustomer());
        $this->assertNull(Registry::getSession()->getVariable('usr'));
    }

    public function testSignInIsSkippedForAnUnknownEMailAddress(): void
    {
        $this->setConfigParam('amazonPayLoginByEMail', Constants::LOGIN_BY_EMAIL_ALL);
        $this->provideSessionChallenge();

        $this->assertFalse($this->loginAmazonCustomer());
        $this->assertNull(Registry::getSession()->getVariable('usr'));
    }

    public function testSignInIsSkippedWhenTheResponseCarriesNoEMailAddress(): void
    {
        $this->createShopUser('');
        $this->setConfigParam('amazonPayLoginByEMail', Constants::LOGIN_BY_EMAIL_ALL);
        $this->provideSessionChallenge();

        $component = oxNew(UserComponent::class);
        $this->assertFalse($component->loginAmazonCustomer(['response' => ['buyer' => []]]));
        $this->assertNull(Registry::getSession()->getVariable('usr'));
    }

    /**
     * The checkout session response of the express flow carries the email address
     * in the same place as the buyer response of the sign-in flow.
     */
    public function testBothAmazonResponseShapesAreAccepted(): void
    {
        $userId = $this->createShopUser('');
        $this->setConfigParam('amazonPayLoginByEMail', Constants::LOGIN_BY_EMAIL_ALL);
        $this->provideSessionChallenge();

        $component = oxNew(UserComponent::class);
        $this->assertTrue($component->loginAmazonCustomer(['response' => ['email' => self::EMAIL]]));
        $this->assertSame($userId, Registry::getSession()->getVariable('usr'));
    }

    private function loginAmazonCustomer(): bool
    {
        $component = oxNew(UserComponent::class);

        return $component->loginAmazonCustomer(
            ['response' => ['buyer' => ['email' => self::EMAIL, 'name' => 'Amazon Buyer']]]
        );
    }

    private function provideSessionChallenge(): void
    {
        $_POST['stoken'] = Registry::getSession()->getSessionChallengeToken();
    }

    private function createShopUser(string $password, string $rights = 'user', int $active = 1): string
    {
        $user = oxNew(User::class);
        $user->setId('amazonlogintestuser');
        $user->assign([
            'oxusername' => self::EMAIL,
            'oxrights'   => $rights,
            'oxactive'   => $active,
            'oxshopid'   => Registry::getConfig()->getShopId(),
            'oxfname'    => 'Amazon',
            'oxlname'    => 'Buyer',
        ]);
        if ($password !== '') {
            $user->setPassword($password);
        } else {
            $user->oxuser__oxpassword = new Field('', Field::T_RAW);
        }
        $user->save();

        // User::save() forces oxrights to 'user' for new accounts, so an admin
        // account can only be set up by writing the column directly
        DatabaseProvider::getDb()->execute(
            'UPDATE oxuser SET oxrights = ? WHERE OXID = ?',
            [$rights, $user->getId()]
        );

        return $user->getId();
    }
}
