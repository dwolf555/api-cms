<?php


namespace APICMS\Controller;


use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;


class FieldController extends AbstractEntityController {

    const SELECT_STATEMENT = 'f.id field_id, f.entity_id, f.name, f.type, DATE_FORMAT(f.created, "%Y-%m-%dT%TZ") as created';

    /**
     * {@inheritdoc}
     */
    public function delete(Application $app, Request $request, $id)
    {
        return $this->deleteEntity($app['db'], 'fields', 'Field', $id);
    }

    /**
     * {@inheritdoc}
     */
    public function post(Application $app, Request $request)
    {
        $input = $request->request->all();

        // validation
        $inputConstraints = new Assert\Collection([
            'entity_id' => new Assert\Range([
                'min' => 0
            ]),
            'name' => new Assert\NotBlank(),
            'type' => new Assert\NotBlank() // todo only allow valid types
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
            $app['db']->insert('fields', $input);
        } catch (UniqueConstraintViolationException $e) {
            return $this->jsonResponse([
                '[name]' => 'This field has already been created for this entity.'
            ], 400);
        }

        $fieldQuery = $app['db']->createQueryBuilder()
            ->select(self::SELECT_STATEMENT)
            ->from('fields', 'f')
            ->where('f.id = :id')
            ->setParameter('id', $app['db']->lastInsertId());
        $field = $app['db']->fetchAssoc($fieldQuery->getSQL(), $fieldQuery->getParameters());

        return $this->jsonResponse($field, 201);
    }

    /**
     * {@inheritdoc}
     */
    public function put(Application $app, Request $request, $id)
    {
        $input = $request->request->all();

        // validation
        $inputConstraints = new Assert\Collection([
            'entity_id' => new Assert\Optional(
                new Assert\Range(['min' => 0])
            ),
            'name' => new Assert\Optional(new Assert\NotBlank()), // todo require at least one of these values? (for all controllers)
            'type' => new Assert\Optional(new Assert\NotBlank()) // todo only allow valid types
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
            $app['db']->update('fields', $input, ['id' => $id]);
        } catch (UniqueConstraintViolationException $e) {
            return $this->jsonResponse([
                '[name]' => 'This field has already been created.'
            ], 400);
        }

        $fieldQuery = $app['db']->createQueryBuilder()
            ->select(self::SELECT_STATEMENT)
            ->from('fields', 'f')
            ->where('f.id = :id')
            ->setParameter('id', $id);
        $field = $app['db']->fetchAssoc($fieldQuery->getSQL(), $fieldQuery->getParameters());

        return $this->jsonResponse($field, 200);
    }


    /**
     * {@inheritdoc}
     */
    public function get(Application $app, Request $request, $id)
    {
        //todo check permissions
        $query = $app['db']->createQueryBuilder()
            ->select(self::SELECT_STATEMENT)
            ->from('fields', 'f')
            ->where('f.id = :field_id')
            ->setParameter('field_id', $id);

        $field = $app['db']->fetchAssoc(
            $query->getSQL(),
            $query->getParameters()
        );

        if (!$field) {
            return $this->jsonResponse(['message' => self::NOT_FOUND_MSG], 404);
        }

        return $this->jsonResponse($field, 200);
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
                ->from('fields', 'f')
        );
    }


}