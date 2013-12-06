{foreach $changelogs as $item}
### {$item.date|date_format:"Y年n月d日"}

{$item.title}

{/foreach}