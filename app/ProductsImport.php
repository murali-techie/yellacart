<?php

namespace App;

use App\Product;
use App\ProductStock;
use App\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;
use Auth;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Brand;

//class ProductsImport implements ToModel, WithHeadingRow, WithValidation
class ProductsImport implements ToCollection, WithHeadingRow, WithValidation, ToModel
{
    private $rows = 0;

    public function collection(Collection $rows)
    {
        $canImport = true;
        if (\App\Addon::where('unique_identifier', 'seller_subscription')->first() != null &&
            \App\Addon::where('unique_identifier', 'seller_subscription')->first()->activated) {
            if (Auth::user()->user_type == 'seller' && count($rows) > Auth::user()->seller->remaining_uploads) {
                $canImport = false;
                flash(translate('Upload limit has been reached. Please upgrade your package.'))->warning();
            }
        }

        if ($canImport) {
            foreach ($rows as $row) {
                $brandId = Brand::whereName($row['brand'])->first()->id;
                
                $sizeId = Attribute::whereName('Size')->first()->id;
                $fabricId = Attribute::whereName('Fabric')->first()->id;
                
                $attributes = [];
                $choiceOptions = [];
                $variants = [];
                $colors = Color::whereIn('code', explode(',', $row['colors']))->get();
                
                if ($row['size']) {
                    array_push($attributes, strval($sizeId));
                    $choiceOptions[] = ['attribute_id' => strval($sizeId), 'values' => explode(',', $row['size'])];
                    $sizes = explode(',', $row['size']);
                    foreach($sizes as $size) {
                        foreach($colors as $color) {
                            array_push($variants, sprintf("%s-%s", $color->name, $size));
                        }
                    }
                }
                
                if ($row['fabric']) {
                    array_push($attributes, strval($fabricId));
                    $choiceOptions[] = ['attribute_id' => strval($fabricId), 'values' => explode(',', $row['fabric'])];
                    $fabrics = explode(',', $row['fabric']);
                    foreach($fabrics as $fabric) {
                        foreach($colors as $color) {
                            array_push($variants, sprintf("%s-%s", $color->name, $fabric));
                        }
                    }
                }
                
                $attributesValue = json_encode($attributes);
                
                $productId = Product::create(
                    [
                        'name' => $row['name'],
                        'added_by' => Auth::user()->user_type == 'seller' ? 'seller' : 'admin',
                        'user_id' => Auth::user()->user_type == 'seller' ? Auth::user()->id : User::where(
                            'user_type',
                            'admin'
                        )->first()->id,
                        'category_id' => $row['category'],
                        'brand_id' => $brandId,
                        'video_provider' => $row['video_provider'],
                        'video_link' => $row['video_link'],
                        'unit' => $row['unit'],
                        'min_qty' => $row['minimum_quantity'],
                        'tags' => $row['tags'],
                        'colors' => json_encode(explode(', ', $row['colors'])),
                        'attributes' => $attributesValue,
                        'choice_options' => json_encode($choiceOptions),
                        'unit_price' => $row['unit_price'],
                        'purchase_price' => $row['purchase_price'] == null ? $row['unit_price'] : $row['purchase_price'],
                        'current_stock' => $row['current_stock'],
                        'discount_start_date' => strtotime($row['discount_start_date']),
                        'discount_end_date' => strtotime($row['discount_end_date']),
                        'discount' => $row['discount'],
                        'discount_type' => strtolower($row['discount_type']),
                        'description' => $row['description'],
                        'pdf' => $row['pdf_specification'],
                        'meta_title' => $row['meta_title'],
                        'meta_description' => $row['meta_description'],
                        'shipping_type' => strtolower($row['free_shipping']) === 'yes' ? 'free' : 'flat_rate',
                        'shipping_cost' => strtolower($row['free_shipping']) === 'yes' ? 0 : $row['shipping_cost'],
                        'is_quantity_multiplied' => $row['is_quantity_multiplied'],
                        'low_stock_quantity' => $row['low_stock_quantity'],
                        'stock_visibility_state' => strtolower($row['show_stock_in_quantity']) === 'yes' ? 'quantity' : (strtolower($row['show_stock_with_text_only']) === 'yes' ? 'text' : 'hide'),
                        'cash_on_delivery' => strtolower($row['cash_on_delivery']) === 'yes',
                        'featured' => strtolower($row['featured']) === 'yes',
                        'todays_deal' => strtolower($row['todays_deal']) === 'yes',
                        'est_shipping_days' => $row['est_shipping_days'],
                        'variations' => json_encode(array()),
                        'slug' => Str::slug($row['slug']) . "-" . Str::random(5),
                        'thumbnail_img' => $this->downloadThumbnail($row['thumbnail_img']),
                    ]
                );
                
                $productId->min_qty = $row['minimum_quantity'];
                $productId->tags = $row['tags'];
                $productId->attributes = $attributesValue;
                $productId->current_stock = $row['current_stock'];
                $productId->discount_start_date = strtotime($row['discount_start_date']);
                $productId->discount_end_date = strtotime($row['discount_end_date']);
                $productId->discount = $row['discount'];
                $productId->discount_type = strtolower($row['discount_type']);
                $productId->description = $row['description'];
                $productId->pdf = $row['pdf_specification'];
                $productId->shipping_type = strtolower($row['free_shipping']) === 'yes' ? 'free' : 'flat_rate';
                $productId->shipping_cost = strtolower($row['free_shipping']) === 'yes' ? 0 : $row['shipping_cost'];
                $productId->is_quantity_multiplied = $row['is_quantity_multiplied'];
                $productId->low_stock_quantity = $row['low_stock_quantity'];
                $productId->stock_visibility_state = strtolower($row['show_stock_in_quantity']) === 'yes' ? 'quantity' : (strtolower($row['show_stock_with_text_only']) === 'yes' ? 'text' : 'hide');
                $productId->cash_on_delivery = strtolower($row['cash_on_delivery']) === 'yes';
                $productId->featured = strtolower($row['featured']) === 'yes';
                $productId->todays_deal = strtolower($row['todays_deal']) === 'yes';
                $productId->est_shipping_days = $row['est_shipping_days'];
                
                $productId->save();
                
                if (!empty($variants)) {
                    foreach ($variants as $variant) {
                        $stock = ProductStock::create(
                            [
                                'product_id' => $productId->id,
                                'qty' => $row['current_stock'] ?? 0,
                                'price' => $row['unit_price'] ?? 0,
                                'variant' => $variant,
                            ]
                        );
                        $stock->variant = $variant;
                        $stock->sku = $row['sku'];
                        $stock->length = $row['length'];
                        $stock->breadth = $row['breadth'];
                        $stock->height = $row['height'];
                        $stock->weight = $row['weight'];
                        $stock->save();
                    }
                } else {
                    $stock = ProductStock::create(
                            [
                                'product_id' => $productId->id,
                                'qty' => $row['current_stock'] ?? 0,
                                'price' => $row['unit_price'] ?? 0,
                                'variant' => '',
                            ]
                        );
                    $stock->sku = $row['sku'];
                    $stock->length = $row['length'];
                    $stock->breadth = $row['breadth'];
                    $stock->height = $row['height'];
                    $stock->weight = $row['weight'];
                    $stock->save();
                }
            }

            flash(translate('Products imported successfully'))->success();
        }
    }

    public function model(array $row)
    {
        ++$this->rows;
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }

    public function rules(): array
    {
        return [
            // Can also use callback validation rules
            'unit_price' => function ($attribute, $value, $onFailure) {
                if (! is_numeric($value)) {
                    $onFailure('Unit price is not numeric');
                }
            }
        ];
    }

    public function downloadThumbnail($url)
    {
        try {
            $extension = pathinfo($url, PATHINFO_EXTENSION);
            $filename = 'uploads/all/'.Str::random(5).'.'.$extension;
            $fullpath = 'public/'.$filename;
            $file = file_get_contents($url);
            file_put_contents($fullpath, $file);

            $upload = new Upload;
            $upload->extension = strtolower($extension);

            $upload->file_original_name = $filename;
            $upload->file_name = $filename;
            $upload->user_id = Auth::user()->id;
            $upload->type = "image";
            $upload->file_size = filesize(base_path($fullpath));
            $upload->save();

            return $upload->id;
        } catch (\Exception $e) {
            // dd($e);
        }
        return null;
    }
}
