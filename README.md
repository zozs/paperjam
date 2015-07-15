paperjam
========

Keeps track of your important scanned documents.

## License
Licensed under the ISC License. See the file LICENSE.

## Installation

### Requirements
* PostgreSQL >= 9.2
* PHP

### Installation steps
TODO

#### Modify path
You may have to modify the `<base>`-tag in `index.html` depending on where you place Paperjam in the web root.

#### Create databases
TODO

#### Configure Apache
Use the following settings to get a correct URL rewriting. Adapt `/srv/http` and `/srv/paperjam` to your own environment.

```
<Directory "/srv/http/paperjam">
	RewriteEngine On

	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^api api.php [QSA,L]

	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^ index.html [QSA,L]
</Directory>

Alias /paperjam/files /srv/paperjam/files
<Directory "/srv/paperjam/files">
	# You probably want the same access control mechanism here as you want on the directory above.
</Directory>
```
