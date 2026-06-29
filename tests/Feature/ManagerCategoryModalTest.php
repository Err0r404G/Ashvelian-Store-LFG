<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerCategoryModalTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_manager_categories_are_listed_with_edit_modals(): void
    {
        $manager = User::where('role', 'manager')->firstOrFail();
        $category = Category::firstOrFail();

        $this->actingAs($manager)
            ->get('/manager/categories')
            ->assertOk()
            ->assertSee('Actions')
            ->assertSee('data-bs-toggle="modal"', false)
            ->assertSee('data-bs-target="#editCategory'.$category->id.'"', false)
            ->assertSee('id="editCategory'.$category->id.'"', false)
            ->assertSee('Save Changes')
            ->assertDontSee('style="min-width:280px;"', false);
    }

    public function test_manager_can_update_category_from_modal_form(): void
    {
        $manager = User::where('role', 'manager')->firstOrFail();
        $category = Category::whereNull('parent_id')->firstOrFail();

        $this->actingAs($manager)
            ->from('/manager/categories')
            ->put(route('manager.categories.update', $category), [
                'name' => 'Elite Training',
                'parent_id' => null,
                'description' => 'Updated from the modal.',
                'image_url' => 'https://example.com/category.jpg',
                'sort_order' => 7,
                'is_active' => 1,
            ])
            ->assertRedirect('/manager/categories')
            ->assertSessionHasNoErrors();

        $category->refresh();

        $this->assertSame('Elite Training', $category->name);
        $this->assertSame('elite-training', $category->slug);
        $this->assertSame(7, $category->sort_order);
        $this->assertTrue($category->is_active);
    }

    public function test_category_cannot_be_its_own_parent(): void
    {
        $manager = User::where('role', 'manager')->firstOrFail();
        $category = Category::whereNull('parent_id')->firstOrFail();

        $this->actingAs($manager)
            ->from('/manager/categories')
            ->put(route('manager.categories.update', $category), [
                'name' => $category->name,
                'parent_id' => $category->id,
                'description' => $category->description,
                'image_url' => $category->image_url,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active ? 1 : 0,
            ])
            ->assertRedirect('/manager/categories')
            ->assertSessionHasErrors('parent_id');
    }

    public function test_manager_categories_page_shows_add_subcategory_modals(): void
    {
        $manager = User::where('role', 'manager')->firstOrFail();
        $category = Category::firstOrFail();

        $this->actingAs($manager)
            ->get('/manager/categories')
            ->assertOk()
            ->assertSee('data-bs-target="#addSubcategory'.$category->id.'"', false)
            ->assertSee('id="addSubcategory'.$category->id.'"', false)
            ->assertSee('Add Subcategory to '.$category->name, false)
            ->assertSee('Create Subcategory');
    }

    public function test_manager_can_create_subcategory(): void
    {
        $manager = User::where('role', 'manager')->firstOrFail();
        $parent = Category::whereNull('parent_id')->firstOrFail();

        $this->actingAs($manager)
            ->from('/manager/categories')
            ->post(route('manager.categories.store'), [
                'name' => 'NextGen Router',
                'parent_id' => $parent->id,
                'description' => 'A subcategory for testing routers.',
                'image_url' => 'https://example.com/router.jpg',
                'sort_order' => 2,
                'is_active' => 1,
            ])
            ->assertRedirect('/manager/categories')
            ->assertSessionHasNoErrors();

        $subcategory = Category::where('name', 'NextGen Router')->first();
        $this->assertNotNull($subcategory);
        $this->assertSame($parent->id, $subcategory->parent_id);
        $this->assertSame('nextgen-router', $subcategory->slug);
        $this->assertSame(2, $subcategory->sort_order);
        $this->assertTrue($subcategory->is_active);
    }

    public function test_manager_categories_page_renders_subcategory_products_dropdown(): void
    {
        $manager = User::where('role', 'manager')->firstOrFail();

        // 1. Create a parent category
        $parent = Category::create([
            'name' => 'Network Equipment',
            'slug' => 'network-equipment',
            'is_active' => true,
            'sort_order' => -100
        ]);

        // 2. Create a subcategory under the parent
        $sub = Category::create([
            'name' => 'Routers',
            'slug' => 'routers',
            'parent_id' => $parent->id,
            'is_active' => true,
            'sort_order' => 1
        ]);

        // 3. Create a product inside the subcategory
        $product = Product::create([
            'category_id' => $sub->id,
            'name' => 'Cisco Nexus Pro Router',
            'slug' => 'cisco-nexus-pro-router',
            'sku' => 'CISCO-NEXUS-PRO',
            'description' => 'Enterprise grade router.',
            'price' => 125000.00,
            'stock_quantity' => 10,
            'status' => 'active',
        ]);

        // 4. Visit /manager/categories and check that the collapse panel details are present
        $this->actingAs($manager)
            ->get('/manager/categories')
            ->assertOk()
            ->assertSee('subcategory-row-for-'.$parent->id, false)
            ->assertSee('Routers')
            ->assertSee('data-bs-target="#editCategory'.$sub->id.'"', false)
            ->assertSee('id="editCategory'.$sub->id.'"', false);
    }
}
