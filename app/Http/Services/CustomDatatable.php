<?php

namespace App\Http\Services;


use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

use function GuzzleHttp\Promise\inspect;

/**
 * Class CustomDataTable
 * @package App\Http\Services
 * 
 * @method static CustomDataTable init() | Initialize the class
 * @method static CustomDataTable of($query) | Set the query
 * @method static CustomDataTable filterData(callable $callable) | Set the filter data
 * @method static CustomDataTable mapDapa(callable $callable) | map the data
 * @method static CustomDataTable mapCollection(callable $callable) | map the collection
 * @method static CustomDataTable addColumn(string $name, callable $callable) | add column
 * @method static CustomDataTable apply($smartSearch = false) | Process Data to order, search, and paginate
 * @method static CustomDataTable done() | return the datatable response
 * @method static CustomDataTable makeJson() | return the datatable response as json
 */
class CustomDataTable
{
  private static $query;
  private static $instance = null;
  private Request $request;
  private $start = 0;
  private $length = 10;
  private $totalData = 0;
  private $total = 0;
  private $draw = 0;
  private $input = [];
  private $isWithNumbering = false;
  private $isFiltered = false;
  private $canUseSmartSearch = false;


  public static function init()
  {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * Constructor
   *
   * @param Builder || @param Model $query
   * 
   * @return Builder || @return Model $query
   *
   */
  public function of($query, $forPrint = false)
  {
    $this->request = request();
    // dd($query);
    $this->canUseSmartSearch = $query instanceof \Illuminate\Database\Eloquent\Model || $query instanceof \Illuminate\Database\Query\Builder || $query instanceof \Illuminate\Database\Eloquent\Builder;

    // dd(DB::getQueryLog());
    if (gettype($query) == 'array') {
      $query = collect($query);
      self::$query = $query;
    } else if ($this->canUseSmartSearch) {
      self::$query = $query;
    } else if ($query instanceof Collection) {
      self::$query = $query;
    } else {
      throw new Exception('Query must be Array, Collection, Query Builder, Eloquent Builder, or Eloquent Model');
    }

    if ($this->request->has('start')) {
      $this->start = $this->request->start;
    }
    if ($this->request->has('length')) {
      $this->length = $this->request->length == 2147483647 ? -1 : $this->request->length;
      // dd($this->length);
    } else if ($forPrint) {
      $this->length = self::$query->count();
    }

    if ($this->request->has('draw')) {
      $this->draw = $this->request->draw;
    }

    $this->input = $this->request->all();

    return $this;
  }

  /**
   * 
   * @param callable $callback
   *
   *  @return Builder
   */
  public function filterData(callable $callable)
  {
    if (is_callable($callable)) {
      self::$query = self::$query->filter(function ($row) use ($callable) {
        return $callable($row);
      });
      return $this;
    } else {
      throw new Exception('Invalid callback');
    }
  }

  public function mapData(callable $callable)
  {
    if (is_callable($callable)) {
      self::$query = self::$query->map(function ($row) use ($callable) {
        return $callable($row);
      });
      return $this;
    } else {
      throw new Exception('Invalid callback');
    }
  }

  public function mapCollection(callable $callable)
  {
    if (is_callable($callable)) {
      self::$query = collect($callable(self::$query));
      return $this;
    } else {
      throw new Exception('Invalid callback');
    }
  }

  public function addIndexColumn()
  {
    $this->isWithNumbering = true;
    return $this;
  }

  public function addColumn($column, $callback)
  {
    self::$query->transform(function ($row) use ($column, $callback) {
      $row->{$column} = $callback($row);
      return $row;
    });
    return $this;
  }

  public function apply($smartSearch = false)
  {
    $this->isFiltered = true;

    if ($smartSearch) {
      $this->smartSearch();
      self::$query = self::$query->get();
    } else {
      $this->search();
    }
    $this->order();

    $this->totalData = self::$query->count();
    $this->total = self::$query->count();

    if ($this->length != -1) {
      self::$query = self::$query->skip($this->start)->take($this->length);
    }

    return $this;
  }

  public function done()
  {
    if (!$this->isFiltered) {
      $this->apply();
    }

    if ($this->isWithNumbering) {
      self::$query->transform(function ($row) {
        $row->DT_RowIndex = ++$this->start;
        return $row;
      });
    }

    $response = [
      'data' => self::$query->values()->all(),
      'draw' => $this->draw,
      'input' => $this->input,
      'recordsTotal' => $this->totalData,
      'recordsFiltered' => $this->total,

    ];
    return $response;
  }

  public function makeJson($withFilter = false, $withLength = false)
  {
    if ($this->canUseSmartSearch && $withFilter) {
      $this->smartSearch();
      self::$query = self::$query->get();
    } else if (!$this->canUseSmartSearch && $withFilter) {
      self::$query = self::$query->get();
      $this->order();
    } else if ($withFilter) {
      $this->search();
      $this->order();
    }

    $this->totalData = self::$query->count();
    $this->total = self::$query->count();

    if ($withLength) {
      self::$query = self::$query->skip($this->start)->take($this->length);
    }

    if ($this->isWithNumbering) {
      self::$query->transform(function ($row) {
        $row->DT_RowIndex = ++$this->start;
        return $row;
      });
    }

    $response = [
      'data' => collect(self::$query->values()->all()),
      'draw' => $this->draw,
      'input' => $this->input,
      'recordsTotal' => $this->totalData,
      'recordsFiltered' => $this->total,
    ];
    return (object) $response;
  }

  private function order()
  {
    if ($this->request->has('order') && is_array($this->request->order)) {
      foreach ($this->request->order as $key => $value) {
        if ($this->request->columns[$value['column']]['orderable'] == 'true') {
          if ($value['dir'] == 'asc') {
            self::$query = self::$query->sortBy($this->request->columns[$value['column']]['data']);
          } else {
            self::$query = self::$query->sortByDesc($this->request->columns[$value['column']]['data']);
          }
        }
      }
    }
  }

  private function search()
  {
    if ($this->request->has('search') && ($this->request->search['value'] !== NULL && $this->request->search['value'] !== '')) {

      $keyword = Str::lower($this->request->search['value']);
      if ($this->request->has('columns')) {

        $searchableColumn = array_filter($this->request->columns, function ($value) use ($keyword) {
          if ($value['searchable'] == 'true') {
            return true;
          }
        });

        self::$query = self::$query->filter(function ($row) use ($keyword, $searchableColumn) {
          $data = $this->serialize($row);

          foreach ($searchableColumn as $index) {
            $column = $index['data'];
            $value = Arr::get($data, $column);
            if (!$value || is_array($value)) {
              continue;
            }

            $value = Str::lower($value);
            if (Str::contains($value, $keyword)) {
              return true;
            }
          }
          return false;
        });
      }
    }
  }

  private function smartSearch()
  {
    if ($this->request->has('search') && ($this->request->search['value'] !== NULL && $this->request->search['value'] !== '')) {

      $keyword = Str::lower($this->request->search['value']);
      if ($this->request->has('columns')) {

        $searchableColumn = array_filter($this->request->columns, function ($value) use ($keyword) {
          if ($value['searchable'] == 'true') {
            return true;
          }
        });
        $queryWhereLike = '$q';
        foreach ($searchableColumn as $key => $value) {
          $queryWhereLike .= '->orwhere("' . $value['data'] . '", "like", "%' . $keyword . '%")';
        }
        $queryWhereLike .= ';';

        self::$query = self::$query->where(function ($q) use ($queryWhereLike) {
          eval('return ' . $queryWhereLike . ';');
        });

        $this->totalData = self::$query->count();
        $this->total = self::$query->count();

        return $this;
      }
    }
  }

  protected function serialize($collection)
  {
    return $collection instanceof Arrayable ? $collection->toArray() : (array) $collection;
  }
}
