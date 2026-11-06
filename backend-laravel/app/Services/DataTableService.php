<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

/**
 * Listing engine — Mountain app/Services/DataTableService.php.
 *
 * Config shape (config/listing/*.php):
 *   fields[].key, fields[].db_field, fields[].search_field, fields[].orderByField
 *   default_order.field / default_order.dir
 *
 * Request (SPA): page, itemsPerPage, sortBy{key,order}, columnSearch, columnFilterSelected
 */
class DataTableService
{
    protected $config;

    protected $fieldDbMapping;

    protected $fieldTypeMapping;

    protected $fieldOrderMapping;

    protected $userDefinedFieldKeyTypeMapping;

    protected $format;

    protected $timezone;

    protected $additionalSelectColumns = [];

    protected $groupBy;

    protected $fieldHavingByMapping;

    protected $externalFilterHandler;

    public function __construct(array $config)
    {
        $this->config = $config;
        foreach ($this->config['fields'] as $field) {
            if (isset($field['userDefinedField']) && $field['userDefinedField']) {
                $this->userDefinedFieldKeyTypeMapping[$field['key']] = $field['fieldTypeId'];
            }
            if (isset($field['db_field'])) {
                $dbField = $field['db_field'];
                if (is_string($dbField) && preg_match('/^(.+)\s+as\s+\w+$/i', $dbField, $aliasedExpression)) {
                    $this->fieldDbMapping[$field['key']] = DB::raw($aliasedExpression[1]);
                }
                else {
                    $this->fieldDbMapping[$field['key']] = $dbField;
                }
                $this->fieldOrderMapping[$field['key']] = $field['orderByField'] ?? $field['db_field'];
            }
            $this->fieldHavingByMapping[$field['key']] = $field['havingDbField'] ?? '';
            $this->fieldTypeMapping[$field['key']] = $field['search_field'] ?? '';
        }
    }

    public function setFormat($format): void
    {
        $this->format = $format;
    }

    public function setTimezone($timezone): void
    {
        $this->timezone = $timezone;
    }

    public function setAdditionalSelectColumns($additionalSelectColumns): void
    {
        $this->additionalSelectColumns = $additionalSelectColumns;
    }

    public function setGroupBy($groupBy): void
    {
        $this->groupBy = $groupBy;
    }

    public function setExternalFilterHandler(callable $handler): void
    {
        $this->externalFilterHandler = $handler;
    }

    public function execute($request, $query, bool $applyPagination = true): array
    {
        $this->applyColumnSelection($query, $request);

        if (isset($request->externalFilterData) && isset($this->externalFilterHandler)) {
            call_user_func($this->externalFilterHandler, $query, $request->externalFilterData);
        }

        $this->applySearch($query, $request);

        if ($this->groupBy != null) {
            $query->groupBy($this->groupBy);
        }

        $this->applySorting($query, $request);

        if ($applyPagination) {
            $page = isset($request->page) && is_numeric($request->page) ? (int) $request->page : 1;
            $itemsPerPage = isset($request->itemsPerPage) && is_numeric($request->itemsPerPage)
                ? (int) $request->itemsPerPage
                : 10;

            return $this->applyPagination($query, $page, $itemsPerPage);
        }

        $data = $query->get();

        return [
            'total' => $data->count(),
            'data' => $data,
        ];
    }

    protected function applyColumnSelection($query, $request): void
    {
        $fieldsToShow = array_filter($this->config['fields'], function ($field) {
            return ! empty($field['db_field']);
        });

        $columns = [];
        foreach ($fieldsToShow as $field) {
            if (! empty($request->columnFilterSelected)) {
                if (in_array($field['key'], $request->columnFilterSelected)) {
                    $columns[] = $field['db_field'];
                }
            }
            else {
                $columns[] = $field['db_field'];
            }
        }

        if ($this->additionalSelectColumns) {
            $columns = array_merge($columns, $this->additionalSelectColumns);
        }

        $expressions = array_filter($columns, fn ($column) => $column instanceof Expression);
        $columns = array_filter($columns, fn ($column) => ! ($column instanceof Expression));

        if (! empty($columns)) {
            $query->selectRaw(implode(', ', $columns));
        }
        foreach ($expressions as $expression) {
            $query->addSelect($expression);
        }
    }

    protected function applySearch($query, $request): void
    {
        if (empty($request->columnSearch)) {
            return;
        }

        if ($this->format == null) {
            $this->format = setDateFormat();
        }

        if ($this->timezone == null) {
            $this->timezone = DateTimeZoneMutator::tenantTimezone();
        }

        foreach ($request->columnSearch as $column => $searchValue) {
            if (empty($searchValue) || (! empty($request->columnFilterSelected) && ! in_array($column, $request->columnFilterSelected))) {
                continue;
            }

            switch ($this->fieldTypeMapping[$column] ?? '') {
                case 'Date':
                    $startDate = $endDate = '';
                    if (strpos($searchValue, 'to') === false) {
                        $startDate = $endDate = $searchValue;
                    }
                    else {
                        [$startDate, $endDate] = explode(' to ', $searchValue);
                    }

                    $startDate = Carbon::parse($startDate, $this->timezone)->startOfDay();
                    $endDate = Carbon::parse($endDate, $this->timezone)->endOfDay();

                    $startDate = DateTimeZoneMutator::convertToBaseDate($startDate, $this->timezone, $this->format);
                    $endDate = DateTimeZoneMutator::convertToBaseDate($endDate, $this->timezone, $this->format);

                    if (isset($this->fieldDbMapping[$column])) {
                        $query->whereBetween($this->fieldDbMapping[$column], [$startDate, $endDate]);
                    }
                    elseif (isset($this->fieldHavingByMapping[$column]) && $this->fieldHavingByMapping[$column] !== '') {
                        $query->havingBetween($this->fieldHavingByMapping[$column], [$startDate, $endDate]);
                    }
                    break;

                case 'Numeric':
                    if (isset($this->fieldDbMapping[$column]) && isset($searchValue['operation'], $searchValue['number'])) {
                        $query->where($this->fieldDbMapping[$column], $searchValue['operation'], $searchValue['number']);
                    }
                    elseif (isset($this->fieldHavingByMapping[$column]) && isset($searchValue['operation'], $searchValue['number'])) {
                        $query->having($this->fieldHavingByMapping[$column], $searchValue['operation'], $searchValue['number']);
                    }
                    break;

                case 'Dropdown':
                    if ($column == 'deleted') {
                        if (in_array(1, $searchValue) && in_array(0, $searchValue)) {
                            // no filter
                        }
                        elseif (in_array(1, $searchValue)) {
                            $query->whereNotNull($this->fieldDbMapping[$column]);
                        }
                        elseif (in_array(0, $searchValue)) {
                            $query->whereNull($this->fieldDbMapping[$column]);
                        }
                    }
                    elseif (isset($this->userDefinedFieldKeyTypeMapping[$column])) {
                        foreach ($searchValue as $value) {
                            $query->orWhereJsonContains($this->fieldDbMapping[$column], ['value' => $value]);
                        }
                    }
                    elseif (isset($this->fieldDbMapping[$column])) {
                        if (is_array($searchValue)) {
                            if (in_array('all', $searchValue)) {
                                $query->whereNotNull($this->fieldDbMapping[$column]);
                            }
                            else {
                                $query->whereIn($this->fieldDbMapping[$column], $searchValue);
                            }
                        }
                        elseif ($searchValue == 'all') {
                            $query->whereNotNull($this->fieldDbMapping[$column]);
                        }
                        else {
                            $query->where($this->fieldDbMapping[$column], $searchValue);
                        }
                    }
                    elseif (isset($this->fieldHavingByMapping[$column]) && $this->fieldHavingByMapping[$column] !== '') {
                        if (! is_array($searchValue)) {
                            $query->having($this->fieldHavingByMapping[$column], $searchValue);
                        }
                    }
                    break;

                default:
                    if (isset($this->fieldDbMapping[$column])) {
                        if (is_array($searchValue)) {
                            $query->whereIn($this->fieldDbMapping[$column], $searchValue);
                        }
                        else {
                            $query->where($this->fieldDbMapping[$column], 'LIKE', '%'.$searchValue.'%');
                        }
                    }
                    elseif (isset($this->fieldHavingByMapping[$column]) && $this->fieldHavingByMapping[$column] !== '') {
                        if (! is_array($searchValue)) {
                            $query->having($this->fieldHavingByMapping[$column], 'LIKE', '%'.$searchValue.'%');
                        }
                    }
                    break;
            }
        }
    }

    protected function applySorting($query, $request): void
    {
        $key = $this->fieldOrderMapping[$this->config['default_order']['field']];
        $order = $this->config['default_order']['dir'];

        $sortBy = null;
        if (isset($request->sortBy) && $request->sortBy) {
            $sortBy = is_array($request->sortBy) ? $request->sortBy : (array) $request->sortBy;
        }
        $columnFilterSelected = isset($request->columnFilterSelected) && is_array($request->columnFilterSelected)
            ? $request->columnFilterSelected
            : [];

        if ($sortBy && isset($sortBy['key'], $sortBy['order']) && in_array($sortBy['key'], $columnFilterSelected, true)) {
            $key = $this->fieldOrderMapping[$sortBy['key']];
            $order = $sortBy['order'];
        }

        foreach ((array) $key as $orderColumn) {
            $query->orderBy($orderColumn, $order);
        }
    }

    protected function applyPagination($query, int $page, int $itemsPerPage): array
    {
        $total = $query->get()->count();
        $results = $query->paginate($itemsPerPage, ['*'], 'page', $page);

        return [
            'total' => $total,
            'data' => $results,
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
        ];
    }
}
