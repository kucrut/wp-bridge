# Bridge
This plugin was built to provide a bridge between WordPress and [Minnie](https://github.com/kucrut/minnie). It modifies the results of some WP API requests.

## Requirements
* WordPress 4.5
* WP API 2.0-beta13

## More bridges:
* [Menus](https://github.com/kucrut/wp-bridge-menus)
* [Post Formats](https://github.com/kucrut/wp-bridge-post-formats)

## Results Modifications
To get a modified result, a "Client ID" must be registered first:

```php
/**
 * Register to the Bridge plugin
 *
 * @param   array  $client_ids Client IDs.
 * @wp_hook filter bridge_client_ids
 * @return  array
 */
function minnie_register_to_bridge( $client_ids ) {
	$client_ids[] = 'minnie';

	return $client_ids;
}
add_filter( 'bridge_client_ids', 'minnie_register_to_bridge' );
```

Then, every API request must include an `X-Requested-With` header and the value set to the registered Client ID, in this case `minnie`.

### Terms
Home URL is stripped from term links.

#### Original Result
```json
[
  {
    "id": 3,
    "count": 0,
    "description": "",
    "link": "http://example.org/blog/type/aside/",
    "name": "Aside",
    "slug": "post-format-aside",
    "taxonomy": "post_format"
  }
]
```

#### Modified Result
```json
[
  {
    "id": 3,
    "count": 0,
    "description": "",
    "link": "/blog/type/aside/",
    "name": "Aside",
    "slug": "post-format-aside",
    "taxonomy": "post_format"
  }
]
```

### Posts
The result of requests to posts endpoints will have these modifications:
* Home URL is stripped from `link`, `content.rendered`.
* `title` gets a new item: `from_content`. It's generated from post content and is useful in case the post doesn't have a title and you still want to display a generated title on the client app.
* `date_formatted` and `modified_formatted` are added, so you don't need to format the post dates in the client app.
* The value of `categories`, `tags` and `formats` is converted to WP_Term objects.

#### Original Result
```json
{
  "id": 1,
  "date": "2016-02-20T02:09:47",
  "date_gmt": "2016-02-20T02:09:47",
  "guid": {
    "rendered": "http://src.wordpress-develop.dev/?p=1"
  },
  "modified": "2016-05-24T11:34:19",
  "modified_gmt": "2016-05-24T11:34:19",
  "slug": "hello-world",
  "type": "post",
  "link": "http://src.wordpress-develop.dev/blog/2016/02/20/hello-world/",
  "title": {
    "rendered": "Hello world!"
  },
  "content": {
    "rendered": "<p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p>\n"
  },
  "excerpt": {
    "rendered": "<p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p>\n"
  },
  "author": 1,
  "featured_media": 0,
  "comment_status": "open",
  "ping_status": "open",
  "sticky": false,
  "format": "aside",
  "categories": [
    1
  ],
  "tags": [
    5
  ],
  "formats": [
    3
  ]
 }
```

#### Modified Result
```json
{
  "id": 1,
  "date": "2016-02-20T02:09:47",
  "date_gmt": "2016-02-20T02:09:47",
  "guid": {
    "rendered": "http://src.wordpress-develop.dev/?p=1"
  },
  "modified": "2016-05-24T11:34:19",
  "modified_gmt": "2016-05-24T11:34:19",
  "slug": "hello-world",
  "type": "post",
  "link": "/blog/2016/02/20/hello-world/",
  "title": {
    "rendered": "Hello world!",
    "from_content": "Welcome to WordPress. This is your first pos…"
  },
  "content": {
    "rendered": "<p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p>\n"
  },
  "excerpt": {
    "rendered": "<p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p>\n"
  },
  "author": 1,
  "featured_media": 0,
  "comment_status": "open",
  "ping_status": "open",
  "sticky": false,
  "format": "aside",
  "categories": [
    {
      "id": 1,
      "name": "Uncategorized",
      "slug": "uncategorized",
      "description": "",
      "link": "/blog/category/uncategorized/"
    }
  ],
  "tags": [
    {
      "id": 5,
      "name": "misc",
      "slug": "misc",
      "description": "",
      "link": "/blog/tag/misc/"
    }
  ],
  "formats": [
    {
      "id": 3,
      "name": "Aside",
      "slug": "post-format-aside",
      "description": "",
      "link": "/blog/type/aside/"
    }
  ],
  "date_formatted": "February 20, 2016",
  "modified_formatted": "May 24, 2016",
}
```

### Attachments
In additions to the modifications above, the result of requests to attachments/media will have these modifications:
* `parent_post` is added.

#### Modified Result
```json
{
  "parent_post": {
    "id": 1,
    "link": "/blog/2016/02/20/hello-world/",
    "title": {
      "rendered": "Hello world!"
    }
  }
}
```

## Changelog
### 0.2.0
* Remove Menus & Post Formats (now as standalone plugins)

### 0.1.0
* Initial
