<?php

use Beam\BeamCore\Actions\Table;

it('can generate a table')
    ->expect(fn ($data) => Table::make($data, 'my table'))->toBe(_table_dump())
    ->with([
        'array'      => [_table_data()],
        'object'     => [(object) _table_data()],
        'collection' => [collect(_table_data())],
        'generator'  => [_table_generator()],
    ]);

function _table_data(): array
{
    return [
        ['id' => 1, 'name' => 'Dev',    'email' => 'Dev@palzin.app'],
        ['id' => 2, 'name' => 'Jay',     'email' => 'Jay@palzin.app'],
        ['id' => 3, 'name' => 'Vikram', 'email' => 'Vikram@palzin.app'],
        ['id' => 4, 'name' => 'Vikas',   'email' => 'Vikas@palzin.app'],
        ['id' => 5, 'name' => 'Anand',   'email' => 'Anand@palzin.app'],
    ];
}

function _table_generator(): iterable
{
    yield from _table_data();
}

function _table_dump(): array
{
    return [
        'fields' => [
            0 => 'id',
            1 => 'name',
            2 => 'email',
        ],
        'values' => [
            0 => [
                'id'    => '1',
                'name'  => 'Luan',
                'email' => 'luan@palzin.app',
            ],
            1 => [
                'id'    => '2',
                'name'  => 'Dan',
                'email' => 'dan@palzin.app',
            ],
            2 => [
                'id'    => '3',
                'name'  => 'Claudio',
                'email' => 'claudio@palzin.app',
            ],
            3 => [
                'id'    => '4',
                'name'  => 'Vitão',
                'email' => 'vitao@palzin.app',
            ],
            4 => [
                'id'    => '5',
                'name'  => 'Anand',
                'email' => 'anand@palzin.app',
            ],
        ],
        'header' => [
            0 => 'id',
            1 => 'name',
            2 => 'email',
        ],
        'label' => 'my table',
    ];
}
