<?PHP
    // PHP Flickr Downloadr
    // --------------------
    // Downloads your entire Flickr library (original size) into one folder per
    // set + an extra folder for photos not in a set. You'll need to fill in your
    // Flickr API values in the define() statements below.
    // --------------------
    // Tyler Hall <tylerhall@gmail.com>
    // January 27, 2009
    // Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>

    // === S T E P  1 ===
    // Fill in your Flickr API information below.
    // You can generate the neccessary values by using phpFlickr's Auth Tool:
    // http://www.phpflickr.com/tools/auth/
    define('API_KEY', '');
    define('API_SECRET', '');

    // === S T E P  2 ===
    // Fill in your Flickr user ID. You can find it here: http://idgettr.com
    define('UID', '');

    // === S T E P  3 ===
    // Run the script via the command line using: "php download-all.php"

    require_once 'vendor/autoload.php';
    $f = new \Samwilson\PhpFlickr\PhpFlickr(API_KEY, API_SECRET);

    // Get all of our photosets
    $sets = $f->photosets()->getList(UID);

    foreach($sets['photoset'] as $set)
    {
        echo "### " . $set['title'] . "\n";
        @mkdir("photos/{$set['title']}", 0777, true);

        // Get all the photos in this set
        $photos = $f->photosets()->getPhotos($set['id'], UID);
        var_dump($photos);

        // And download each one...
        foreach($photos['photo'] as $photo)
        {
            $url = $f->photos()->getLargestSize($photo['id'])['source'];
            var_dump($url);

            if(!is_null($url))
            {
                $dir = escapeshellarg("photos/{$set['title']}");
                $filename = parse_url($url, PHP_URL_PATH);

                // Only download if file does not exist...
                if(!file_exists("photos/{$set['title']}/$filename"))
                    shell_exec("cd $dir; /usr/bin/curl -O $url");
            }

            // This helps stop the Flickr API from getting angry
            sleep(1);
        }
    }

    // Download all photos not in a set
    echo "### No Set\n";
    $photos = $f->photos_getNotInSet();
    foreach($photos['photos']['photo'] as $photo)
    {
        $url = null;
        $sizes = $f->photos_getSizes($photo['id']);
        foreach($sizes as $size)
        {
            if($size['label'] == 'Original')
                $url = $size['source'];
        }

        if(isset($url))
        {
            @mkdir("photos/No Set", 0777, true);
            $dir = escapeshellarg("photos/No Set");
            shell_exec("cd $dir; /usr/bin/curl -O $url");
        }
    }

    echo "Done!\n";
