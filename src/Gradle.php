<?php
namespace WillFarrell\AlfredPkgMan;

require_once('Cache.php');
require_once('Repo.php');

class Gradle extends Repo
{
    protected $id         = 'gradle';
    protected $kind       = 'libraries';
    protected $url        = 'https://bintray.com/bintray/jcenter';
    protected $search_url = 'https://bintray.com/search?query=';

    public function search($query)
    {
        if (!$this->hasMinQueryLength($query)) {
            return $this->xml();
        }

        $this->pkgs = $this->cache->get_query_json(
            $this->id,
            $query,
            "https://api.bintray.com/search/packages?name={$query}"
        );
        
        foreach ($this->pkgs as $pkg) {
            // make params
            $title = "{$pkg->name} (v{$pkg->latest_version})";

            $groupId;
            if (count($pkg->system_ids) > 0) {
                $groupId = $pkg->system_ids[0];
            }

            $url = "";
            $details = "";
            if ($groupId !== null) {
                $url = "{$this->url}/{$groupId}";
                $details = "GroupId: {$groupId}";
            } else {
                $url = "{$this->url}/{$pkg->name}";
                $details = "No GroupId found for package.";
            }

            $this->cache->w->result(
                $pkg->name,
                $this->makeArg($pkg->id, $url, $groupId),
                $title,
                $details,
                "icon-cache/{$this->id}.png"
            );

            // only search till max return reached
            if (count($this->cache->w->results()) == $this->max_return) {
                break;
            }
        }

        $this->noResults($query, "{$this->search_url}{$query}");

        return $this->xml();
    }
}

// Test code, uncomment to debug this script from the command-line
// $repo = new Gradle();
// echo $repo->search('leaflet');
