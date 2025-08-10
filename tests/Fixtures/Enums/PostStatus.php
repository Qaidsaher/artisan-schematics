<?php
namespace Saher\ArtisanSchematics\Tests\Fixtures\Enums;

enum PostStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
}