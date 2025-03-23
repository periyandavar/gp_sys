<?php

/**
 * Helper
 */

namespace System\Helper;

/**
 * Pagination Helper
 */
class PaginationHelper
{
    /**
     * Function will generates the Table head for passed columns
     *
     * @param array $columns Columns
     *
     * @return string
     */
    public static function generateTh(array $columns)
    {
        $th = '';
        foreach ($columns as $column) {
            $th .= "<th> $column </th>";
        }

        return $th;
    }

    /**
     * Generates the Paginations
     *
     * @param array  $pagination Pagination details
     * @param string $url        base url
     *
     * @return string
     */
    public static function generatePagination($pagination, $url): string
    {
        $code = '';
        if ($pagination['tpages'] > 1) {
            $code .= ($pagination['start'] == 1)
            ? '<li class="disable"><a class="disable">Previous</a></li>'
            : '<li><a href="' . $url
                 . '?index=' . ((($pagination['cpage'] - 1)) * $pagination['limit'])
                 . '&limit=' . $pagination['limit'] . '&search=' . $pagination['search']
                 . '">Previous</a></li>';
            $code .= ($pagination['start'] == 1)
            ? '<li class="active"><a>1</a></li>'
            : '<li><a href="' . $url
                . '?index=0&limit=' . $pagination['limit']
                . '&search=' . $pagination['search'] . '">1</a></li>';
            if ($pagination['tpages'] > 6 && $pagination['cpage'] > 4) {
                $i = $pagination['cpage'];
                $iEnd = $pagination['cpage'] + 3;
                $iEnd = $pagination['tpages'] < $iEnd
                    ? $pagination['tpages']
                    : $iEnd;
            } else {
                $i = 2;
                $iEnd = $pagination['tpages'] < 6 ? $pagination['tpages'] : 6;
            }
            if ($i != 2) {
                $code .= "<li class='disable'>...</li>";
            }
            for (; $i < $iEnd; $i++) {
                $li = '<li';
                $li = ($i == $pagination['cpage'] + 1)
                ? $li . " class='active'><a>$i</a></li>"
                : $li . "><a href='$url?index=" . ($pagination['limit'] * ($i - 1))
                        . '&limit=' . $pagination['limit'] . '&search='
                        . $pagination['search'] . "'>$i</a></li>";
                $code .= $li;
            }
            if ($i != $pagination['tpages']) {
                $code .= "<li class='disable'>...</li>";
            }
            $code .= ($pagination['end'] == $pagination['tcount'])
            ? '<li class="active"><a>' . $pagination['tpages'] . '</a></li>'
            : '<li><a href="' . $url
                 . '?index=' . (($pagination['tpages'] - 1) * $pagination['limit'])
                 . '&limit=' . $pagination['limit'] . '&search=' . $pagination['search']
                 . '">' . $pagination['tpages'] . '</a></li>';
            $code .= ($pagination['end'] == $pagination['tcount'])
            ? '<li class="disable"><a class="disable">Next</a></li>'
            : '<li><a href="' . $url . '?index='
                . ((($pagination['cpage'] + 1) * $pagination['limit']))
                . '&limit=' . $pagination['limit'] . '&search=' . $pagination['search']
                . '">Next</a></li>';
        }

        return $code;
    }
}
