<?php

namespace App\Http\Repository\Task;

interface BaseInterface
{
  public function model($model): self;
  public function queryModel($model);
  public function with($args);
  public function firstOrNew();
  public function firstOrFail();
  public function whereFirst($column, $value);
  public function when($bool, $callable): self;
  public function where($column, $value): self;
  public function whereLike($column, $value): self;
  public function orderby($column, $order): self;
  public function getAll();
}
