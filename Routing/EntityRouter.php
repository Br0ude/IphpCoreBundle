<?php
/**
 * Created by Vitiko
 * Date: 02.08.12
 * Time: 10:26
 */

namespace Iphp\CoreBundle\Routing;

use Iphp\CoreBundle\Model\Rubric;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class EntityRouter
{

    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }


    public function getRouter()
    {
        return $this->router;
    }

    public function generateEntityActionPath($entity, $action = 'view', $params = array())
    {

        $routeName = $this->routeNameForEntityAction($entity, $action);

        return $this->generateEntityPath($entity, $routeName, $params);
    }






    public function generateEntityPath($entity, $routeName, $params = array())
    {
        if (strpos($routeName, 'view') && !$params) {
            $params = array('id' => method_exists($entity, 'getSlug') ? $entity->getSlug() : $entity->getId());
        }

        $path = 'route-name-not-found-' . $routeName;

        try {
            $path = $this->router->generate($routeName, $params);
        } catch (\Exception $e) {
        }

        return $path;
    }

    public function routeNameForEntityAction($entity, $action, Rubric $rubric = null)
    {
        if ($action == '') $action = 'view';

        if (is_object($entity)) {

            $entityClassName = \Doctrine\Common\Util\ClassUtils::getClass($entity);

            $entityPart = str_replace('\\', '', str_replace('Entity\\', '',  $entityClassName));
        } else {
            //Todo: Хак, нужно использовать kernel->getBundle(..)->getNamespace() но доступа к kernel пока нет
            //list ($bundleName, $entityName) = explode (':',$entity);

            $entityPart = str_replace(':', '', $entity);
        }

        return $entityPart . '_' . lcfirst($action);
    }


    public function entitySitePath($entity, $arg1 = null, $arg2 = null, $arg3 = null)
    {

        if (is_array($entity)) {

            $params = isset($entity[1]) ? $entity[1] : array();
            $entity = $entity[0];
        }


        if (is_object($entity)) {


            if ($entity instanceof \Iphp\TreeBundle\Model\TreeNodeWrapper) $entity = $entity->getWrapped();

            if (!method_exists($entity, 'getSitePath')) {
                return 'method ' . get_class($entity) . '->getSitePath() not defined';
                throw new \Exception ('method ' . get_class($entity) . '->getSitePath() not defined');
            }

            $methodData = new \ReflectionMethod($entity, 'getSitePath');
            $parameters = $methodData->getParameters();

            $args = array($arg1, $arg2, $arg3);
            if (sizeof($parameters) > 0 && $parameters[0]->getClass() &&
                $parameters[0]->getClass()->isInstance($this)
            ) {
                array_unshift($args, $this);
            }


            //Нельзя автоматом подставлять $this->rubricManager->getBaseUrl() т.к. метод getSitePath\
            //может использовать entityRouter который сгенерирует путь уже с базовым урлом (app_dev.php)

            $path = call_user_func_array(array($entity, 'getSitePath'), $args);
        } else {

            $path = $this->generateEntityActionPath($entity, $arg1, $params);


        }

        return $path;
    }
}
