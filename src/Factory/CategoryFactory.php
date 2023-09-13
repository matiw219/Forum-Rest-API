<?php

namespace App\Factory;

use App\Entity\Category;
use App\Entity\User;

class CategoryFactory
{
    public static function create(string $name, ?Category $parent, ?User $creator): Category
    {
        $category = new Category();
        $category->setName($name);
        $category->setParent($parent);
        $category->setCreatedAt(new \DateTimeImmutable());
        $category->setCreatedBy($creator);

        return $category;
    }

}