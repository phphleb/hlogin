<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Helpers;

final class UserSearchHelper
{
    /**
     * Returns processed data based on filters to create a query.
     *
     * Возвращает обработанные данные по фильтрам для составления запроса.
     *
     * @internal
     */
    public static function getFilterData(array $filters = []): array
    {
        $where = '';
        $list = [];
        // Valid fields for filtering.
        // Допустимые поля для фильтрации.
        $listName = ['id', 'email', 'login', 'name', 'surname', 'phone', 'address', 'promocode', 'ip'];
        foreach ($filters as $key => $value) {
            if (!in_array($key ?? '', $listName) ||
                !preg_match('/^[0-9a-zA-Z\_\ \.\@\-\:\;\&]{1,50}$/', $value ?? '')
            ) {
                break;
            }
            $where .= " {$key} LIKE :{$key} AND";
            $list[$key] = "%{$value}%";
        }
        return [$where, $list];
    }

    /**
     * Converts page data into a block with pagination buttons.
     *
     * Преобразует данные страницы в блок с кнопками пагинации.
     *
     * @internal
     */
    public static function getPagination(int $pages, int $page, string $range = '...', string $text = ''): string
    {
        $selfFn = static function (int $value) use ($text): string {
            return "<button class='hlogin-az-page-btn'>$text $value</button>";
        };

        $linkFn = static function (int $value, string $name): string {
            return "<button class='hlogin-az-page-btn-link' data-value='$value'>$name</button>";
        };

        // Only one page.
        // Только одна страница.
        if ($pages < 2) {
            return '';
        }
        // Two pages.
        // Две страницы.
        if ($pages === 2) {
            // Two pages and the first one is selected.
            // Две страницы и выбрана первая.
            if ($page === 1) {
                return $selfFn(1) . $linkFn(2, "2");
            }
            // Two pages and the second one is selected.
            // Две страницы и выбрана вторая.
            if ($page === 2) {
                return $linkFn(1, "1") . $selfFn(2);
            }
        }
        // Three pages.
        // Три страницы.
        if ($pages === 3) {
            // Three pages and the first one is selected.
            // Три страницы и выбрана первая.
            if ($page === 1) {
                return $selfFn(1) . $linkFn(2, "2") . $linkFn($pages, "3");
            }
            if ($page === 2) {
                return $linkFn(1, "1") . $selfFn(2) . $linkFn($pages, "3");
            }
            // Three pages and the last one is selected.
            // Три страницы и выбрана последняя.
            if ($page === 3) {
                return $linkFn(1, "1") . $linkFn(2, (string)($page - 1)) . $selfFn(3);
            }
        }
        // Many pages and the first one is selected.
        // Много страниц и выбрана первая.
        if ($page < 2) {
            return $selfFn(1) . $linkFn(2, "2") . $linkFn($pages, "$range $pages");
        }
        // Many pages and the last one is selected.
        // Много страниц и выбрана последняя.
        if ($page === $pages) {
            return $linkFn(1, "1 $range") . $linkFn(($page - 1), (string)($page - 1)) . $selfFn($page);
        }
        // Many pages and the penultimate one is selected.
        // Много страниц и выбрана предпоследняя.
        if ($page === $pages - 1) {
            return $linkFn(1, "1 $range") . $linkFn(($page - 1), (string)($page - 1)) . $selfFn($page) . $linkFn($pages, "$pages");
        }
        // Many pages and the second one is selected.
        // Много страниц и выбрана вторая.
        if ($page === 2) {
            return $linkFn(1, "1") . $selfFn(2) . $linkFn(($page + 1), (string)($page + 1)) . $linkFn($pages, "$range $pages");
        }
        // There are many pages and one of them is selected (not the first and not the last).
        // Много страниц и выбрана одна из них (не первая и не последняя).
        return $linkFn(1, "1 $range") . $linkFn(($page - 1), (string)($page - 1)) . $selfFn($page) . $linkFn(($page + 1), (string)($page + 1)) . $linkFn($pages, "$range $pages");
    }

    /**
     * Block sorting -1 (descending), 0 (no sorting), 1 (ascending).
     *
     * Сортировка блока -1 (по убыванию), 0 (без сортировки), 1 (по возрастанию).
     */
    public static function getSortBlock(string $type, int $sort = 0): string
    {
        return "<span class=\"hlogin-az-sort-block\" data-type=\"$type\">" . match ($sort) {
            -1 => "<button class=\"hlogin-az-sort-btn\" data-value=\"1\">&#9650;</button> " .
                "<button class=\"hlogin-az-sort-btn hlogin-az-sort-select\"  data-value=\"-1\">&#9660;</button>",
            1 => "<button class=\"hlogin-az-sort-btn hlogin-az-sort-select\" data-value=\"1\" data-type=\"$type\">&#9650;</button> " .
                "<button class=\"hlogin-az-sort-btn\" data-value=\"-1\">&#9660;</button>",
            default => "<button class=\"hlogin-az-sort-btn\" data-value=\"1\">&#9650;</button> ".
                        "<button class=\"hlogin-az-sort-btn\" data-value=\"-1\">&#9660;</button>"
        } . "</span>";
    }
}