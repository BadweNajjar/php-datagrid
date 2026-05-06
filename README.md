# PHP DataGrid (CodeIgniter 4 fork)

Version: 8.6.x

A fork of PHP DataGrid modernized for **PHP 7.4+ / 8.x** and rewritten to use
the **CodeIgniter 4 database layer** natively. PDO, `mysql_*`, and all legacy
adapters (oci, ibm, ibase, firebird, sybase, mssql, odbc) have been removed.

Supported drivers (via CI4's database adapters):

| Legacy short name | CI4 adapter |
|-------------------|-------------|
| `mysql`           | `MySQLi`    |
| `pgsql`           | `Postgre`   |
| `sqlsrv`          | `SQLSRV`    |
| `sqlite`          | `SQLite3`   |

Ready-to-use CI4 controllers live in
[examples/controllers/](examples/controllers/).

---

## 1. Library layout

The library cleanly separates server-side code from browser-served assets:

```
datagrid/
├── src/                           ← server-side (PHP loads from disk)
│   ├── DataGrid.php               ← main class, namespace DataGrid\
│   ├── Helper.php
│   ├── SqlParser.php
│   ├── Asset/                     ← inline-asset helpers
│   ├── Database/                  ← CI4 connection bridge
│   ├── Export/                    ← CSV / XLS / PDF / DOC / XML exporters
│   ├── Filter/                    ← FilterBuilder
│   ├── Render/                    ← Tabular / Columnar / Customized / Paging / ControlPanel
│   ├── Security/                  ← SecurityCheck
│   ├── languages/                 ← i18n (one .php file per locale)
│   ├── js/                        ← inlined runtime scripts (dg.js, dg_ajax.js, …)
│   ├── styles/                    ← inlined runtime CSS (themes)
│   ├── modules/                   ← PHP-only modules included server-side (tFPDF)
│   └── tmp/                       ← cache + export staging (must be writable)
│
├── public/                        ← browser-served (must be web-reachable)
│   ├── images/                    ← icons (sort arrows, mimetype glyphs, cal.gif, …)
│   ├── modules/                   ← browser-loaded modules (jQuery, jsCalendar, lytebox,
│   │                                jscolor, wysiwyg, autosuggest, scrolling, jsafv,
│   │                                and `calendar/calendar.php` opened by window.open)
│   └── scripts/                   ← download.php / download_blob.php endpoints
│
└── code_template.php              ← starter template
```

Key idea:

- Everything in `src/` is loaded by PHP straight from disk. It never needs to
  be web-reachable — keep it outside the document root.
- Everything in `public/` is referenced by the browser via `<img src=…>`,
  `<script src=…>`, etc. It **must** be served as static files.

---

## 2. Install into your CI4 application

### a. Copy `src/` into your app's libraries

```powershell
Copy-Item -Recurse datagrid\src  <ci4>\app\Libraries\DataGrid\src
```

### b. Publish `public/` under your CI4 document root

```powershell
Copy-Item -Recurse datagrid\public\*  <ci4>\public\datagrid\
```

After this step you should have:

```
<ci4>/public/datagrid/images/...
<ci4>/public/datagrid/modules/...
<ci4>/public/datagrid/scripts/...
```

### c. Make `tmp/` writable

```powershell
icacls <ci4>\app\Libraries\DataGrid\src\tmp\cache  /grant "IIS_IUSRS:(OI)(CI)M"
icacls <ci4>\app\Libraries\DataGrid\src\tmp\export /grant "IIS_IUSRS:(OI)(CI)M"
```

---

## 3. Register the namespace

In `app/Config/Autoload.php`:

```php
public $psr4 = [
    APP_NAMESPACE => APPPATH,
    'Config'      => APPPATH . 'Config',
    'DataGrid'    => APPPATH . 'Libraries/DataGrid/src',  // ← add this
];
```

---

## 4. Configure the database

Set up your default group in `app/Config/Database.php` as you would for any
CI4 app. The DataGrid will pick it up automatically when called without an
explicit handle.

For the bundled examples, import the dump first:

```sql
-- examples/sql/db_dump.sql
```

---

## 5. Use it from a controller

The shortest possible controller:

```php
<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use DataGrid\DataGrid;

class Countries extends BaseController
{
    public function index()
    {
        service('session');                         // DataGrid uses $_SESSION

        $dgrid = new DataGrid(false, true, 'c_');

        // Tell the grid where its public/ assets are reachable from the browser:
        $dgrid->SetPublicUrl(base_url('datagrid'));

        // Auto-load the default CI4 DB group:
        $dgrid->DataSource(
            "SELECT id, name, description FROM demo_countries",
            ['name' => 'ASC']
        );

        $dgrid->SetCaption('Countries');
        $dgrid->SetTableEdit('demo_countries', 'id');
        $dgrid->SetAutoColumnsInViewMode(true);
        $dgrid->SetAutoColumnsInEditMode(true);

        $dgrid->Bind();
    }
}
```

### `DataSource()` call styles

```php
// 1. Auto-load default CI4 DB group:
$dgrid->DataSource($sql, $defaultOrder);

// 2. Pass an existing CI4 connection:
$db = \Config\Database::connect();        // or connect('analytics')
$dgrid->DataSource($db, $sql, $defaultOrder);
```

---

## 6. Routes

Most DataGrid actions (sort, paginate, switch to add/edit/details, save) post
back to the same URL with `_mode` parameters. Register **both GET and POST**
on each grid endpoint:

```php
$routes->match(['get', 'post'], 'countries', 'Countries::index');
```

---

## 7. Asset URL & path overrides

The grid keeps two separate path properties for the `public/` folder:

| Method                            | What it sets                        | Default                                   |
|-----------------------------------|-------------------------------------|-------------------------------------------|
| `SetPublicUrl($url)`              | browser URL prefix to `public/`     | `'public/'` (or `DATAGRID_DIR.'public/'`) |
| `SetPublicPath($filesystemPath)`  | filesystem path to `public/`        | `<src>/../public/`                        |

Typical CI4 usage:

```php
$dgrid->SetPublicUrl(base_url('datagrid'));
// SetPublicPath() is only needed when the public/ folder lives somewhere
// other than directly next to src/, e.g.:
// $dgrid->SetPublicPath(FCPATH . 'datagrid/');
```

> `SetAssetsUrl()` is kept as a deprecated alias of `SetPublicUrl()` for
> back-compat. Prefer the new name in new code.

---

## 8. Working examples (copy/paste ready)

Eleven CI4 controllers covering the original demo grids live under
[examples/controllers/](examples/controllers/):

| Controller                                                   | Demo                              |
|--------------------------------------------------------------|-----------------------------------|
| [Code11Example.php](examples/controllers/Code11Example.php)  | Simplest grid                     |
| [Code12Example.php](examples/controllers/Code12Example.php)  | Master-detail                     |
| [Code21Example.php](examples/controllers/Code21Example.php)  | Custom view-mode columns          |
| [Code22Example.php](examples/controllers/Code22Example.php)  | Add/edit/details mode             |
| [Code23Example.php](examples/controllers/Code23Example.php)  | Field validation                  |
| [Code24Example.php](examples/controllers/Code24Example.php)  | Filtering + advanced filters      |
| [Code25Example.php](examples/controllers/Code25Example.php)  | Multi-row operations              |
| [Code26Example.php](examples/controllers/Code26Example.php)  | Foreign keys / lookups            |
| [Code27Example.php](examples/controllers/Code27Example.php)  | Export to CSV / Excel / PDF / XML |
| [Code28Example.php](examples/controllers/Code28Example.php)  | Customized view-mode layout       |
| [Code29Example.php](examples/controllers/Code29Example.php)  | File uploads                      |

Suggested route block:

```php
$routes->group('examples', static function ($routes) {
    foreach (['1-1','1-2','2-1','2-2','2-3','2-4','2-5','2-6','2-7','2-8','2-9'] as $n) {
        $cls = 'Code'.str_replace('-','',$n).'Example';
        $routes->match(['get','post'], "code-$n", "Examples\\$cls::index");
    }
});
```

> Each controller wraps the original demo body verbatim. Add
> `$dgrid->SetPublicUrl(base_url('datagrid'));` immediately after the
> `new DataGrid(...)` line if you publish assets at `<ci4>/public/datagrid/`.

---

## 9. Notes & caveats

- All CSS, JavaScript, and bundled module assets that the browser fetches are
  served as ordinary static files from `public/`. They are **not** inlined.
  Theme CSS, language strings and the small `dg.*` runtime scripts under
  `src/{styles,languages,js}/` are inlined by PHP and don't need to be
  web-reachable.
- `<src>/tmp/cache/` and `<src>/tmp/export/` must be writable by PHP (cached
  language strings, generated exports).
- File-upload examples ([Code29Example.php](examples/controllers/Code29Example.php))
  expect a writable `uploads/` directory; create one under `public/` and adjust
  the `SetFieldsEditPropertiesArray()` paths to match.
- The legacy standalone PHP demo files have been removed — the controllers
  above are the canonical examples.
- The old web-based installer in `examples/install/` is retained only as a
  convenience for seeding `examples/sql/db_dump.sql` into a fresh database;
  it is **not** required for CI4 use.

---

## License

GNU Lesser General Public License — see [license/](license/).
