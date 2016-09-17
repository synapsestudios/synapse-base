### Testing a Controller With PHPUnit

To test a controller, extend [ControllerTestCase](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/TestHelper/ControllerTestCase.php).
This class contains helper methods for making requests, mocking validation results, and simulating a logged in user. The following is an example that does all three.
The full file can be found [here](https://github.com/synapsestudios/api.puppies.com/blob/develop/tests/Test/Application/Listing/ListingControllerTest.php).

```php
namespace Test\Application\Listing;

use Application\Listing\ListingController;
use Synapse\TestHelper\ControllerTestCase;
use stdClass;

class ListingControllerTest extends ControllerTestCase
{
    public function setUp()
    {
        $this->captured = new stdClass();

        $this->setUpMockSecurityContext();

        $this->setMocks([
            'listingService'     => 'Application\Listing\ListingService',
            'listingValidator'   => 'Application\Listing\ListingValidator',
            'listingPhotoMapper' => 'Application\Listing\ListingPhotoMapper',
        ]);

        $this->controller = new ListingController(
            $this->mocks['listingService'],
            $this->mocks['listingValidator'],
            $this->mocks['listingPhotoMapper'],
            $this->getListingSearchQueryWithMockLocationMapperInjected()
        );

        $this->controller->setSecurityContext($this->mocks['securityContext']);
    }

    // ...

    public function withLoggedInUserNotVerified()
    {
        $user = $this->getDefaultLoggedInUserEntity();

        $user->setVerified(false);

        $this->setLoggedInUserEntity($user);
    }

    public function withValidatorValidateReturningErrors()
    {
        $errors = $this->createNonEmptyConstraintViolationList();

        $this->mocks['listingValidator']->expects($this->any())
            ->method('validate')
            ->will($this->returnValue($errors));
    }

    public function testPostReturns422IfValidationConstraintsAreViolated()
    {
        $this->withValidatorValidateReturningErrors();

        $data     = $this->getListingData();
        $request  = $this->getPostRequest($data);
        $response = $this->controller->execute($request);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testPostReturns403IfUserNotVerifiedAndStatusNotDraft()
    {
        $this->withLoggedInUserNotVerified();

        $request = $this->createJsonRequest('post', [
            'content' => ['status' => 'review']
        ]);

        $response = $this->controller->execute($request);

        $this->assertEquals(403, $response->getStatusCode());
    }
}
```
