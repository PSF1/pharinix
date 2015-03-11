# pharinix

Installation:

You must copy the files to your local server for access by http://localhost/pharinix
Load the file "files/pharinix alfa YYYYMMDD HHII.sql" to your database.
Configure MySQL connection in etc/pharinix.config.php.

Then you can view the CMS running. In the Direct command box try tu put: rewriteBasicList

Information:

This is a alpha version and it must grow, it is lacks of options and functions. It don't have users, groups, security, hocks, etc... All of it will come soon.

The background concept it's the use of minimalist commands, bits of code, that we can reuse easy with minimal footprint in resources. The source code have some comments, I recommed read it.

Database information:
* Table `bin-path`

List of paths to groups to commands. The core search command in this paths.

* Table `node_type`

Node types definitions. Only contains name of type and some log information. Fields named created, creator_node_user, modifier and modifier_node_user contains creation and modification dates and ID of the future user node_type.

* Table `node_type_field`

`name`: Field name

`type`: Type of the field, it can be a basic type, 'longtext', 'bool', 'datetime', 'double', 'integer, 'string', or a node type name

`len`: Length of field, if it's applicable

`required`: is a required field?

`readonly`: is a not writeble field?

`locked`: is a system field?

`node_type`: ID of owner node type

`default`: Default value

`label`: Label in form, or table headers

`help`: Help about the field

`multi`: If type is not a basic type, it can link some other records. (One to any relation)

* Table `pages`

`name`: Page name, it's used how ID to some commands

`template`: Relative path to the template, ex. templates/pages/default.xml

`title`: Title of the page

`description`: Meta description, it's for search indexers

`keys`: Meta key words, it's for search indexers

* Table `page-blocks`

`idpage`: Numeric ID of the page owner of the block. A zero ID is for show in all pages, that have the idcol selected

`idcol`: ID of the column. In the template editor you can see/modify this IDs

`command`: The command to execute (case sensitive on linux)

`parameters`: Post parametes to apply to the command, it must be URL encoded

`priority`: If a block have more than one command, this value change the execution order

* Table `url_rewrite`

`url`: Relative URL to rewrite.

`rewriteto`: Post parameters to apply when rewrite the URL, it must be URL encoded and include the parameter 'command'

All the tables must have a indexed key field named 'id'.

Notes:
I encourage you to read first the tests files, and then read the source code.
For now, Pharnix don't have user control because I like make user definition how a node type.

Copyright (C) 2015 Pedro Pelaez <aaaaa976@gmail.com>
Sources https://github.com/PSF1/pharinix

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

I used some libs from other, soon I will put it here and/or in a page into the CMS's default content.