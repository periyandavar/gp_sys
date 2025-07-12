<?php

/**
 * BaseRestController
 */

namespace System\Core\Base\Controller;

use Database\Orm\Record;
use Loader\Container;
use Router\Request\Request;
use Router\Response\Response;
use System\Core\Data\DataRecord;

/**
 * Super class for all rest based controller. All rest basef controllers should
 * extend this controller
 * BaseRestController class consists of basic level functions for various purposes
 */
abstract class RestController extends Controller
{
    protected string $modelClass = '';

    /**
     * Handles GET requests
     *
     * @return DataRecord|null
     */
    public function view($id)
    {
        $model = $this->getModelName();

        return $model::find($id);
    }
    /**
     * Handles POST request
     *
     * @return DataRecord|Response|null
     */
    public function create(DataRecord $model)
    {
        $model = $this->getDataModel();

        if ($model->validate() === false) {
            /**
             * @var Response
             */
            $response = Container::get(Response::class);
            $response->setStatusCode(400);
            $response->setBody(json_encode([
                'message' => 'Validation failed',
                'errors' => $model->getErrors(),
            ]));

            return $response;
        }
        $model->save();

        return $model->reload();
    }
    /**
     * Handles PUT request
     *
     * @return DataRecord
     */
    public function update($id, DataRecord $model)
    {
        $model = $this->getDataModel();
        $old_model = $this->view($id);
        if (!$old_model) {
            throw new \Exception('Model not found with id: ' . $id);
        }
        $old_model->setValues($model->getValues());
        $old_model->save(true);

        return $old_model;
    }

    /**
     * Handles DELETE request
     *
     * @return void
     */
    public function delete($id)
    {
        $model = $this->view($id);
        if (!$model) {
            throw new \Exception('Model not found with id: ' . $id);
        }
        $model->delete();

        return;
    }

    /**
     * Handles PATCH request
     *
     * @return DataRecord|Response|null
     */
    public function patch(int $id, DataRecord $model)
    {
        $model = $this->getDataModel();
        $old_model = $this->view($id);
        if (!$old_model) {
            throw new \Exception('Model not found with id: ' . $id);
        }

        $updateValues = array_diff_assoc(
            $model->getValues(),
            $old_model->getValues(),
        );
        unset($updateValues[$model::getUniqueKey()]);
        if (empty($updateValues)) {
            return $old_model;
        }
        $old_model->setValues($updateValues);
        $old_model->save(true);

        return $old_model->reload();
    }

    public function list()
    {
        $model = $this->getModelName();

        return $model::findAll();
    }

    protected function getModelName()
    {
        if (empty($this->modelClass)) {
            throw new \Exception('Model is not set for ' . static::class);
        }

        return $this->modelClass;
    }

    protected function getDataModel()
    {
        $modelName = $this->getModelName();
        if (!class_exists($modelName)) {
            throw new \Exception('Model class does not exist: ' . $modelName);
        }
        $model = new $modelName();

        if (!($model instanceof Record)) {
            throw new \Exception('Invalid Model class: ' . $modelName);
        }

        $request = Container::get(Request::class);
        $data = $request->post();
        $data = empty($data) ? $request->data() : $data;
        $model->setValues($data);

        return $model;
    }
}
