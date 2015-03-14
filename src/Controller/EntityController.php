<?php


namespace APICMS\Controller;


use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;


class EntityController extends AbstractEntityController {

    const SELECT_STATEMENT = 'e.id, e.name, DATE_FORMAT(e.created, "%Y-%m-%dT%TZ") as created';

    /**
     * {@inheritdoc}
     */
    public function delete(Application $app, Request $request, $id)
    {
        return $this->deleteEntity($app['db'], 'entities', 'Entity', $id);
    }

    /**
     * {@inheritdoc}
     */
    public function post(Application $app, Request $request)
    {
        $input = $request->request->all();

        // validation
        $inputConstraints = new Assert\Collection([
            'name' => new Assert\NotBlank()
        ]);
        $errors = $app['validator']->validateValue($input, $inputConstraints);

        if (count($errors) > 0) {
            $errorArray = [];
            foreach ($errors as $e) {
                $errorArray[$e->getPropertyPath()] = $e->getMessage();
            }
            return $this->jsonResponse($errorArray, 400);
        }

        // create role
        try {
            $app['db']->insert('entities', $input);
        } catch (UniqueConstraintViolationException $e) {
            return $this->jsonResponse([
                '[name]' => 'This entity has already been created.'
            ], 400);
        }

        $entityQuery = $app['db']->createQueryBuilder()
            ->select(self::SELECT_STATEMENT)
            ->from('entities', 'e')
            ->where('e.id = :id')
            ->setParameter('id', $app['db']->lastInsertId());
        $entity = $app['db']->fetchAssoc($entityQuery->getSQL(), $entityQuery->getParameters());

        return $this->jsonResponse($entity, 201);
    }

    /**
     * {@inheritdoc}
     */
    public function put(Application $app, Request $request, $id)
    {
        $input = $request->request->all();

        // validation
        $inputConstraints = new Assert\Collection([
            'name' => new Assert\Optional(new Assert\NotBlank())
        ]);
        $errors = $app['validator']->validateValue($input, $inputConstraints);

        if (count($errors) > 0) { // todo abstract this bit
            $errorArray = [];
            foreach ($errors as $e) {
                $errorArray[$e->getPropertyPath()] = $e->getMessage();
            }
            return $this->jsonResponse($errorArray, 400);
        }

        // create role
        try {
            $app['db']->update('entities', $input, ['id' => $id]);
        } catch (UniqueConstraintViolationException $e) {
            return $this->jsonResponse([
                '[name]' => 'This entity has already been created.'
            ], 400);
        }

        $entityQuery = $app['db']->createQueryBuilder()
            ->select(self::SELECT_STATEMENT)
            ->from('entities', 'e')
            ->where('e.id = :id')
            ->setParameter('id', $id);
        $entity = $app['db']->fetchAssoc($entityQuery->getSQL(), $entityQuery->getParameters());

        return $this->jsonResponse($entity, 200);
    }


    /**
     * {@inheritdoc}
     */
    public function get(Application $app, Request $request, $id)
    {
        //todo check permissions
        $query = $app['db']->createQueryBuilder()
            ->select(self::SELECT_STATEMENT)
            ->from('entities', 'e')
            ->where('e.id = :entity_id')
            ->setParameter('entity_id', $id);

        $entity = $app['db']->fetchAssoc(
            $query->getSQL(),
            $query->getParameters()
        );

        if (!$entity) {
            return $this->jsonResponse(['message' => self::NOT_FOUND_MSG], 404);
        }

        return $this->jsonResponse($entity, 200);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(Application $app, Request $request)
    {
        return $this->paginate(
            $app,
            $app['db']->createQueryBuilder()
                ->select(self::SELECT_STATEMENT)
                ->from('entities', 'e')
        );
    }


}