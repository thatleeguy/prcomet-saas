<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->columns(2)
                    ->components([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, callable $set) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            })
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL: /articles/your-slug')
                            ->columnSpanFull(),

                        Textarea::make('excerpt')
                            ->rows(2)
                            ->maxLength(500)
                            ->helperText('Short summary shown on listing cards and used as the default meta description.')
                            ->columnSpanFull(),

                        MarkdownEditor::make('body_md')
                            ->label('Body')
                            ->helperText('Use ## and ### headings — the on-page index is built from them.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Publishing')
                    ->columns(2)
                    ->components([
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'unpublished' => 'Unpublished',
                            ])
                            ->default('draft')
                            ->required(),

                        DateTimePicker::make('published_at')
                            ->label('Publish date')
                            ->helperText('Leave empty to publish immediately when status is Published.'),

                        TextInput::make('category')
                            ->maxLength(255)
                            ->helperText('Primary topic — drives /articles/topic/{category}.'),

                        TagsInput::make('tags')
                            ->helperText('Optional keywords.'),

                        TextInput::make('author_name')
                            ->default('PrComet Team')
                            ->maxLength(255),
                    ]),

                Section::make('SEO')
                    ->description('Optional overrides for search engines and social cards.')
                    ->collapsed()
                    ->columns(2)
                    ->components([
                        TextInput::make('meta_title')
                            ->maxLength(70)
                            ->helperText('Defaults to the title. Aim for under 60 characters.')
                            ->columnSpanFull(),

                        Textarea::make('meta_description')
                            ->rows(2)
                            ->maxLength(160)
                            ->helperText('Defaults to the excerpt. Aim for ~155 characters.')
                            ->columnSpanFull(),

                        FileUpload::make('og_image_path')
                            ->label('Social share image')
                            ->image()
                            ->directory('articles/og')
                            ->helperText('Recommended 1200×630.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
