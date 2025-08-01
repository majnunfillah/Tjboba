<?php

namespace App\Http\Repository;

use App\Http\Repository\Task\BaseInterface;
use stdClass;

class BaseRepository implements BaseInterface
{
  protected $model;
  protected $db;
  private $nmspc = 'App\Models\\';

  public function __construct($args = NULL)
  {
    $this->model = new stdClass();
    if ($args !== NULL) {
      if (is_array($args)) {
        foreach ($args as $value) {
          $this->model->{strtolower($value)} = $value;
        }
      } else {
        $this->model->{strtolower($args)} = $args;
      }
    }
  }

  public function model($model): self
  {
    if (!property_exists($this->model, strtolower($model))) {
      $this->model->{strtolower($model)} = strtoupper($model);
    }
    $class = $this->nmspc . $this->model->{$model};
    $this->model->{$model} = new $class;
    $this->db = strtolower($model);
    return $this;
  }

  public function queryModel($model)
  {
    if (!property_exists($this->model, $model)) {
      $this->model->{$model} = strtoupper($model);
      $class = $this->nmspc . $this->model->{$model};
      $this->model->{$model} = new $class;
      return $this->model->{$model};
    } else if (property_exists($this->model, $model) && !is_object($this->model->{$model})) {
      $class = $this->nmspc . $this->model->{$model};
      $this->model->{$model} = new $class;
      return $this->model->{$model};
    } else {
      return $this->model->{$model};
    }
  }

  public function with($args): self
  {
    $this->model->{$this->db} = $this->model->{$this->db}->with($args);
    return $this;
  }

  public function firstOrNew()
  {
    return $this->model->{$this->db}->firstOrNew();
  }

  public function firstOrFail()
  {
    return $this->model->{$this->db}->firstOrFail();
  }

  public function whereFirst($column, $value)
  {
    return $this->model->{$this->db}->where($column, $value)->first();
  }

  public function when($bool, $callable): self
  {
    if ($bool) {
      $this->model->{$this->db} = $callable($this->model->{$this->db});
    }
    return $this;
  }

  public function where($column, $value): self
  {
    $this->model->{$this->db} = $this->model->{$this->db}->where($column, $value);
    return $this;
  }

  public function whereLike($column, $value): self
  {
    $this->model->{$this->db} = $this->model->{$this->db}->where($column, 'like', '%' . $value . '%');
    return $this;
  }

  public function orderby($column, $order): self
  {
    $this->model->{$this->db} = $this->model->{$this->db}->orderBy($column, $order);
    return $this;
  }

  public function getAll()
  {
    return $this->model->{$this->db}->get();
  }
}
