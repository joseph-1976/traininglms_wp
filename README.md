# traininglms_wp

Please start by viewing WP_DB_Object.php, as it is the brain of my evils. 

Training Management Plugin - Word Press (OOP)
This plugin allows for a LMS (Learning Mangement System) to be integrated within Word Press. The plugin is built with OOP and uses advanced templating and routing.

#Framework Base Layer or Interface

Web based frameworks provide the tools and libraries needed to create a web based application with minimal efforts.  For PHP, there are numerous, perhaps bordering on countless, of such frameworks available (Symphony and Phalcon).  They typically provide:
	a router mechanism which decomposes the request URL and calls registered request handlers if a match is found,
	some form of MVC type architecture for translating requests into viewable data, 
	an asset managament toolkit to handle all the js, css, and html fiiles that contribute to the view, and, lastly, but probably not least important, some form of a DB abstraction interface.

WordPress has many of the underlying components of a web based framework but they are internalized to servicing requests and delivering data most often associated with one block content, such as those of blog related posts.  These underlying features can be tapped /and utilized/via/XintoX with the publically available event framework s and various   functions that are made public for the use of xcreatingx plugins and/or modifing WordPress behavior from the aspect of the outside looking in.  For Training LMS project, it has been possible to synthesize a web framework, or web like framework, from this open, available functionality.

##DB
Perhaps the biggest challange faced by users of WordPress for complex content is the restrictions imposed by the database.  Every entry in the DB (excluding Users) is ultimately considered a Post and fields comprising a Post are available whether they are needed or not.  Fields that are needed beyond the base Post table must be added to the postmeta table which consists of the ID of the parent Post, the field name and the field value.  A complex Custom Post Type will ultimately end up with numerous entries in the postmeta table.  While on the surface this may not seem like a major problem, acessing the data of the entity in a unified fashion is impossible due to its disjointed nature.  There is also the problem of running meaningful SQL queries which we will touch on later.

To combat these problems, the TTP-LMS introduces an Object DB Layer between core code (ie. classes) and the WP DB Abstraction used in accessing the post and postmeta tables (eg. wp_update_post, wp_insert_post, add_post_meta, update_post_meta,...).  Any Class that has a persistence need can pass a schema representing required fields to the DB Layer and automatically be provided with an Object Relational Map.  Creating Classes that extend from an existing ORM classes is made possible by simply extending the parent Class and addomh tp ots its schema.

By default, any WP_DB_Object automatically has access to one field, the underlying post ID.  Any fields, even those available via the post table, must be specified in the schema.  Classes must provide a Post Type but only one Post Type is needed in an inheritance chain since Classes types are differianted separately.  Classes must also provide a prefix for field names but again, only one prefix is needed in an inheritance chain.  Each object instance is saved with its Class type.  (It is possible for object's to have multiple types as in User, but this is currently not supported.)

Objects are created by the 'create' method which takes an associate array of key values pairs where the key's name represents the schema field name not the Object field name.  As an example, for a field named 'short_description' in the class Course, an Object with a corresponding DB entry would be created with `\TheTrainingPartners\Course::create( array( 'short_description' => 'xxx' ) );`  After creation, changes to this field would be made with camel case: $object->getShortDescription(); and $object->setShortDescription('xxx');  This may change in the future. (See discussion below.)

Objects are retrieved by the 'instance' method using the corresponding post ID and maybe instantiated from any Class in the Objects heirarchy.  As such, a LiveCourse can be retrieved via `\TheTrainingMangerLMS\Course::instance($id);

All fields are read into the underlying Object's Class cache on creation or retrieval.  Changes made via set methods are automatically applied to the underlying DB tables unless the 'cache_updates' option is specified with the cakk ro during either creation or retrieval.

Writing SQL for our WP_DB_Object (or WordPress Posts in general) is severaly complicated by the two table approach.  For each field accessed in the query, it is necessary to JOIN to the postmeta table, which can lead to many multiple JOIN statements.

SELECT * FROM wp_posts p
JOIN wp_postmeta m ON (p.ID = m.post_id)
JOIN wp_postmeta m2 ON (p.ID = m2.post_id)
WHERE p.ID = 864 AND p.post_type = 'Course' AND m.meta_key = 'ttp_lms_course_type' and m.meta_value = '\\TheTrainingMangerLMS\\LiveCourse'
AND m2.meta_key = 'ttp_lms_course_startdate';

If it is necessary to follow relations between Objects in the DB, the underlying SQL becomes even more complicated:
SELECT * FROM wp_posts p
JOIN wp_postmeta m ON (p.ID = m.post_id)
JOIN wp_postmeta m2 ON (p.ID = m2.post_id)
JOIN wp_posts p2 ON (p2.ID = m2.meta_value)...

Outstanding is the need to separate logic representing the Class from those of the DB from WP_DB_Object.  Even though there will be additional classes, this will simplify code greatly.  As there are currently no issues from this paradigym composite, there is no need to pursue this separation.

##Routing
WordPress automatically decompiles the URL and attempts to make sense of what is being requested.  For this process to run smoothly, it is necessary to dictate various rewrite rules, endpoints, and such/similiar to help instruct WordPress how to interpret these URLs, specifically for one's plugin.
In the process, WordPress attempts to find a suitable template in the corresponding theme to field the request via template file name itself.  This consists of looking at keywords that match various aspects of the request, such as is it a post, page, or complex post type, is it for multiple entities, single entities, the home page, etc.  This ulimately putcome leads to a phlethora of template files in the theme folder where only a few are needed.

For our plugin, we wish to maintain the content generation to the confines of the ttp plugin directory.  It is also important that we be able to insert Javascript and CSS files at the appropriate times in the page generation cycle.  In order to accomplish this, a mechanism has been created where ContentGenerators register themselves during TTP-LMS initialization.  During the WordPress cycle, the ContentGenerators are queried for a match to the current request and if a match is found, they are "bound" to the current request.  The CG's, themselves, are responsible for setting up appropriate actions for JavaScript, CSS, and html templates, not the plugin.  Essentially this allows us to create the notion of subthemes in our plugin.  The theme can be changed without requiring the need to regenerate bucket loads of files.

Not yet implemented is the ability to capture errors generated in the content generation phase and modify accordingly.  Not hard to do.

It may also be necessary to sanitize and validate requests before WordPress has made an attempt at deciphering the request.  The number of scenarios where this functionality is needed becomes more prevalent as the complexity of the URLs increase.  There is currently nothing in the code base for this scenario but provisions have been made for the eventual need.

##Ajax
Marshalling of methods defined by a specific namespace.  Wrapped environment, returns http status codes indicative of the underlying error, exceptions get their own custom code.  Works extremely well for client side user notification.



