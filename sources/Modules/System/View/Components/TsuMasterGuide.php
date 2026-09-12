<?php

namespace Modules\System\View\Components;

use Illuminate\View\Component;

class TsuMasterGuide extends Component
{
    public string $title;
    public string $description;
    public array $connections;
    public ?string $impact;
    public bool $collapsible;
    public string $guideId;

    /**
     * Create a new component instance.
     *
     * @param string $title
     * @param string $description
     * @param array $connections [ ['label' => '...', 'route' => '...', 'url' => '...', 'icon' => '...', 'badge' => '...'] ]
     * @param string|null $impact
     * @param bool $collapsible
     * @param string|null $guideId
     */
    public function __construct(
        string $title = 'Panduan Keterkaitan Master Data',
        string $description = '',
        array $connections = [],
        ?string $impact = null,
        bool $collapsible = true,
        ?string $guideId = null
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->connections = $connections;
        $this->impact = $impact;
        $this->collapsible = $collapsible;
        $this->guideId = $guideId ?: 'guide-' . substr(md5($title), 0, 8);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('system::components.tsu-master-guide');
    }
}
