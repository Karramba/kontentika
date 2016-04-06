<?php
namespace AppBundle\Data\Loader;

use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;

class RemoteStreamLoader implements LoaderInterface
{
    protected $imagine;

    public function __construct(ImagineInterface $imagine)
    {
        $this->imagine = $imagine;
    }

    public function find($path)
    {
        // The http:// becomes http:/ in the url path due to how routing urls are converted
        // so we need to replace it by http:// in order to load the remote file
        $path = preg_replace('@\:/(\w)@', '://$1', $path);
        return $this->imagine->load(file_get_contents($path));
    }
}
