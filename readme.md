# LexoRankTrait - A Trait for Managing Sortable Positions in Laravel Models

The `LexoRankTrait` is a trait that simplifies the process of managing sortable positions within your Laravel models. It provides functionality for dynamically calculating and managing the "position" or "rank" of items in a list. This is useful for scenarios where you want to reorder items without relying on fixed indices.

## Features

- **Automatic Positioning on Create**: When creating a new record, the trait will automatically assign it a position based on the existing records.
- **Customizable Query Logic**: You can customize the way the sorting is applied, allowing for more flexible scenarios such as filtering by additional fields.
- **Reordering Models**: You can move models before or after other models, and the positions will be automatically adjusted.
- **Flexible Sorting**: Easily sort models based on the sortable field (`position` by default, but this can be customized).
- **Custom Sortable Field**: The trait uses `position` by default but allows customization to any other field that holds the sortable value.

## Installation

1.  **Add the trait to your model**:

    In order to use `LexoRankTrait`, simply include it in your model:

    ```php
    use Dede\Lexorank\LexoRankTrait;

    class CustomItem extends Model
    {
        use LexoRankTrait;

        // Optionally override the default sortable field
        protected static $sortableField = custom_position;
    }
    ```

2.  **Override the applySortableGroup method (optional)**:

    If you want to apply custom query conditions (e.g., sorting within groups like `mogou_id` or `sub_mogou_id`), you can override the `applySortableGroup` method in your model:

    ```php

    //example custom applySortableGroup 
    protected static function applySortableGroup(QueryBuilder $query, Model $model)
    {
        if (property_exists($model, 'category_id') && property_exists($model, 'active')) {
            $query->where('category_id', $x)
                  ->where('active',1);
        }

        return $query;
    }
    ```

3.  **Run Migrations**:

    Ensure that the table youre using with the trait has a sortable field (like `position`) to store the rank. If your model uses `position`, make sure the database schema is ready to store it:

    ```php
    Schema::table(custom_items, function (Blueprint $table) {
        $table->string('position')->nullable();

        $table->index('position'); // recommend
    });
    ```

## Methods

1.  `bootLexoRankTrait()`

    - The traits `bootLexoRankTrait()` method automatically assigns the next available position when creating a new model. You can customize how the max position is calculated by overriding the `applySortableGroup` method.

2.  `scopeSorted($query)`

    - Use this scope to retrieve models ordered by their position field.

    Example:

    ```php
    $items = CustomItem::sorted()->get();
    ```

3.  `moveAfter($entity)`

    - Moves the current model after another model (recalculating positions accordingly).

    Example:

    ```php
    $firstItem->moveAfter($secondItem);
    ```

4.  `moveBefore($entity)`

    - Moves the current model before another model (recalculating positions accordingly).

    Example:

    ```php
    $firstItem->moveBefore($secondItem);
    ```

5.  `resetPositions`

    - Resets the positions of all models in the table.

    Example:

    ```php
    CustomItem::resetPositions();
    ```

6.  `getNewPosition($prev, $next, $isMoving = false)`

    - Generates a new position based on the positions of two other models.

7.  `previous($limit = 0)`

    - Gets the previous models relative to the current model.

8.  `next($limit = 0)`

    - Gets the next models relative to the current model.

9.  `siblings($isNext, $limit = 0)`

    - Retrieves models that are either before or after the current model based on the position field.

10. `getPrevious($limit = 0)`


    - Gets a collection of the previous models.

11. `getNext($limit = 0)`

    - Gets a collection of the next models.

## Example Usage

```php
use App\Models\CustomItem;

// Creating a new item
$item = CustomItem::create([
    name => New Item,
    // Any other attributes
]);

// Reordering an item
$item1 = CustomItem::find(1);
$item2 = CustomItem::find(2);

// Move item1 before item2
$item1->moveBefore($item2);

// Move item1 after item2
$item1->moveAfter($item2);

// Get next items (with a limit)
$nextItems = $item1->getNext(5);

// Get previous items (with a limit)
$previousItems = $item1->getPrevious(5);
```

## Customizing the Sortable Field

If you want to use a different field as the sortable field, you can override the `$sortableField` property in your model:

```php
class CustomItem extends Model
{
    use LexoRankTrait;

    // Override the default sortable field to rank
    protected static $sortableField = rank;
}
```

## Notes

- **Performance Considerations**: When dealing with large datasets, be mindful of how the position is being calculated and the number of queries executed. You may want to consider adding appropriate indexes to the sortable field.
- **Migration Strategy**: If youre introducing this functionality into an existing project, consider how the existing data should be migrated and ranked. A script to calculate and update the position values might be necessary.

## License

MIT License
