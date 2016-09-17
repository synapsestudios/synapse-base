# [Route Variable Converter](http://silex.sensiolabs.org/doc/usage.html#route-variables-converters)

## What is it?

A route variable converter performs a transformation on a route attribute before the Request object reaches the controller.

For example, a route at `/users/<user_id>` might have use a converter to convert the `user_id` string into a `UserEntity`. There are two benefits of using a converter in this case:

1. The controller does not have to perform the transformation.
1. Any other controllers needing to perform the same transformation can use the converter, thereby preventing [code duplication](http://c2.com/cgi/wiki?DontRepeatYourself)

## How to use it

See the [Silex documentation](http://silex.sensiolabs.org/doc/usage.html#route-variables-converters) for instructions.

Note that the documentation gives two ways to reference the converter function itself:

1. A raw anonymous function
1. A reference to the service and method in the format `service:method`.

In the spirit of loose coupling and modularity, it's our policy to use option 2. Converters should always be packaged as a separate class and defined in a service provider. This works particularly well for cases in which the converter requires a dependency such as a mapper to be injected.

### Using Mappers Directly [id => Entity]

Perhaps the most helpful and common use case of a converter is to convert an entity ID into the entity object itself. This is the exact purpose of a mapper. In fact, the [`FinderTrait`](mapper.md#synapsemapperfindertrait) contains a convenience method that performs this exact operation: `findById`.

Since that's the case, the mapper can be used directly *as* a converter for this use case:

```PHP
$app->match('/users/{user}', 'user.controller:rest')
    ->method('GET|PUT|DELETE')
    ->bind('user-entity')
    ->assert('user', '\d+')
    ->convert('user', 'user.mapper:findById');
```

## Example

In this example, an endpoint exists for editing a blog. But the system should only allow the creator of the blog to edit it. So the converter searches for a blog with the given ID, and the current user as its creator.

Note that the example converter uses `SecurityAwareInterface` and `SecurityAwareTrait`. By `implement`ing and `use`ing these, the currently logged in user entity can be accessed with `$this->user()`. Beyond this fact, it's not necessary to understand this functionality. (For reference, this is achieved with [initializers](initializer.md).)

### BlogConverter
```PHP
<?php

namespace Application\Blog;
use Synapse\Security\SecurityAwareInterface;
use Synapse\Security\SecurityAwareTrait;

class BlogConverter implements SecurityAwareInterface
{
    use SecurityAwareTrait;

    /**
     * @var BlogMapper
     */
    protected $mapper;

    /**
     * @param BlogMapper $mapper
     */
    public function __construct(BlogMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function getBlogCreatedByCurrentUser($blogId)
    {
        $currentUserId = $this->user()->getId();

        return $this->mapper->findBy([
            'id'        => $blogId,
            'author_id' => $currentUserId,
        ]);
    }
}
```

### BlogMapper
```PHP
<?php

namespace Application\Blog;

use Synapse\Mapper;

class BlogMapper extends Mapper\AbstractMapper
{
    use Mapper\FinderTrait;

    /**
     * {@inheritDoc}
     */
    protected $tableName = 'blogs';
}
```

### BlogController
```PHP
<?php

namespace Application\Blog;

use Synapse\Controller\AbstractRestController;
use Symfony\Component\HttpFoundation\Request;

class BlogController extends AbstractRestController
{
    /**
     * Get a blog by ID
     *
     * @param  Request $request
     * @return Response
     */
    public function get(Request $request)
    {
        $blogEntity = $request->attributes->get('blog');

        if ($blogEntity === false) {
            return $this->createNotFoundResponse();
        }

        return $this->createEntityResponse($blogEntity);
    }
}
```

### BlogServiceProvider
```PHP
<?php

namespace Application\Blog;

use Silex\ServiceProviderInterface;
use Silex\Application;

class BlogServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app['blog.converter'] = $app->share(function ($app) {
            $mapper = $app['blog.mapper'];

            return new BlogConverter($mapper);
        });

        $app['blog.mapper'] = $app->share(function ($app) {
            return new BlogMapper($mapper);
        });

        $app['blog.controller'] = $app->share(function ($app) {
            return new BlogController();
        });

        $this->defineRoutes();
    }

    /**
     * Define blog-related routes
     *
     * @param  Application $app
     */
    protected function defineRoutes(Application $app)
    {
        $app->match('/blogs/{blog}', 'blog.controller:rest')
            ->method('GET')
            ->assert('blog', '\d+')
            ->convert('blog', 'blog.converter:getBlogCreatedByCurrentUser')
            ->bind('blog-entity');
    }
}
```
