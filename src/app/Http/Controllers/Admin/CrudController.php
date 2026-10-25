<?php

namespace GemaDigital\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController as OriginalCrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Database\Eloquent\Model;
use ReflectionMethod;

class CrudController extends OriginalCrudController
{
    public const CREATED = 'create';

    public const CLONED = 'clone';

    public const DESTROYED = 'destroy';

    public const REORDERED = 'reorder';

    public const UPDATED = 'update';

    // Hack to access setup without touching the setup()
    public function setupConfigurationForCurrentOperation(?string $operation = null): void
    {
        parent::setupConfigurationForCurrentOperation($operation);

        // Check authorization for CRUD
        $id = $this->crud->getCurrentEntryId();

        if ($id && ! $this->authorize($id)) {
            abort(401);
        }

        // Bulk and reorder operations send their entries in the request, not in the route.
        // Only when the controller has its own authorize(): without one nothing changes
        if ((new ReflectionMethod($this, 'authorize'))->class !== self::class) {
            foreach ($this->getRequestEntryIds() as $entryId) {
                if ($entryId === null || ! $this->authorize($entryId)) {
                    abort(401);
                }
            }
        }
    }

    /**
     * Authorize access to an entry.
     * Runs for the entry in the route and for each entry sent by the bulk (delete, clone) and reorder operations.
     * It doesn't run on create, where there is no entry yet: use CRUD::denyAccess('create') for that.
     */
    public function authorize(int $id): bool
    {
        return true;
    }

    /**
     * Get the ids sent in the request by the bulk and reorder operations.
     * Integer keys are cast as the database does ("12abc" finds entry 12 on MySQL); null when it isn't an id.
     *
     * @return array<int, int|string|null>
     */
    protected function getRequestEntryIds(): array
    {
        $ids = match ($this->crud->getCurrentOperation()) {
            'bulkDelete', 'bulkClone' => (array) request()->input('entries', []),
            'reorder' => array_column((array) json_decode((string) request()->input('tree'), true), 'item_id'),
            default => [],
        };

        $intKey = in_array($this->crud->model->getKeyType(), ['int', 'integer'], true);

        return array_map(
            fn (mixed $id): int|string|null => is_scalar($id) ? ($intKey ? (int) $id : (string) $id) : null,
            array_values(array_filter($ids)),
        );
    }

    public function wantsJSON(): bool
    {
        return str_starts_with((string) request()->headers->get('accept'), 'application/json');
    }

    private $i = 0;

    public function separator(?string $title = null): CrudPanel
    {
        return CRUD::addField([
            'name' => 'separator'.$this->i++,
            'type' => 'custom_html',
            'value' => '<hr />'.($title ? "<h2>$title</h2>" : ''),
            'wrapperAttributes' => [
                'style' => 'margin:0',
            ],
        ]);
    }

    public function getEntryID(): int
    {
        preg_match('/\w+\/(\d+)/', (string) $_SERVER['REQUEST_URI'], $matches);

        return $matches && count($matches) > 1 ? intval($matches[1]) : null;
    }

    public function getEntry(): Model
    {
        return $this->crud->model::where('id', $this->getEntryID())->first() ?: null;
    }

    // Overrides to deal with cache
    public function sync(string $operation): void {}
}
