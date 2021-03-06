<?php
namespace tsframe\module\menu;

class MenuItem{
	protected $title;
	protected $data = [];
	protected $children = [];

	public function __construct(?string $title = null, array $data = []){
		$this->setTitle($title);
		$this->data = $data;
	}

	public function add(MenuItem $menu, int $index = -1){
		ksort($this->children);
		
		if ($index >= 0){
			$pre = array_slice($this->children, 0, $index);
			$post = array_slice($this->children, $index);
		} else {
			$sizeof = sizeof($this->children) + 1;
			$pre = array_slice($this->children, 0, $sizeof + $index);
			$post = array_slice($this->children, $sizeof + $index);
		}

		$this->children = array_merge($pre, [$menu], $post);

		return $this;
	}

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(?string $title){
        $this->title = $title;
    }

    public function getData(string $item){
        return $this->data[$item] ?? null;
    }

    public function setData(string $key, $value){
        $this->data[$key] = $value;
    }

    public function getChildren(): array {
        return $this->children;
    }

    public function hasChildren(): bool {
        return sizeof($this->children) > 0;
    }
}