<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\Tree;

use deepeloper\Debeetle\Loader;
use deepeloper\Debeetle\Tree\Exception\DifferentParts;
use deepeloper\Debeetle\Tree\Exception\InvalidPlaceException;

/**
 * Tabs tree.
 */
class Tree
{
    /**
     * Developer mode flag
     *
     * @var bool
     */
    protected $developerMode;

    /**
     * Array of tabs content
     */
    protected $content = [];

    protected $locales = [];

    /**
     * Tab id to caption
     */
    protected $captions = [];

    /**
     * The pointer to the current tab
     *
     * @var Node
     */
    protected $current;

    /**
     * Last tab name
     *
     * @var string
     */
    protected $last = "";

    /**
     * Last pointer
     *
     * @var mixed
     * @see Debeetle_Tree_Manager::setPointer()
     */
    protected $pointer;

    /**
     * Array of disabled tabs
     *
     * @var array
     */
    protected $disabledTabs;

    /**
     * Flag specifying to check for disabled tabs
     *
     * @var bool
     */
    protected $checkDisabled = false;

    /**
     * Empty tree node
     *
     * @var EmptyNode
     */
    protected $emptyElement;

    /**
     * Constructor
     *
     * @param array $settings  Array containing debug settings
     */
    public function __construct(array $settings = [])
    {
        $this->developerMode = !empty($settings['developerMode']);
        if (isset($settings['disabled']['tab'])) {
            $this->disabledTabs = (array)$settings['disabled']['tab'];
            $this->checkDisabled = !empty($this->disabledTabs);
            if ($this->checkDisabled) {
                $this->emptyElement = new EmptyNode('', false);
            }
        }
        $this->locales = $settings['locales'];
        $this->current = $this->emptyElement;
    }

    public function addLocales(array $locales, $override = false)
    {
        $this->locales = $override ? $locales + $this->locales : $this->locales + $locales;
    }

    /**
     * Create/select tab.
     *
     * @param string $id Tab id
     * @param ?string $caption Tab caption
     * @param ?array $places Target places (f.e. ["before:tabId", "after:tabId", "start:tabId", "end:tabId", "anywhere"])
     * @param bool $active  Specifies tab activity
     * @return void
     * @throws DifferentParts
     * @throws InvalidPlaceException  When cannot add tab at specified places, developer mode only.
     * @todo Remove $active argument?
     */
    public function select($id, $caption = null, ?array $places = null, $active = false)
    {
        $explodedId = explode("|", $id);
        if (null === $caption) {
            foreach ($explodedId as $partialId) {
                if (!isset($this->captions[$partialId])) {
                    $this->captions[$partialId] =
                        isset($this->locales["tab-$partialId"]) ? $this->locales["tab-$partialId"] : $partialId;
                }
            }
        } else {
            $explodedCaption = explode("|", $caption);
            if (sizeof($explodedId) !== sizeof($explodedCaption)) {
                Loader::onError(
                    "$id -> $caption",
                    "DifferentParts"
                );
            }
            foreach ($explodedId as $index => $partialId) {
                $this->captions[$partialId] = $explodedCaption[$index];
            }
        }

        if ($this->setPointer($id, null === $places, $active)) {
            $this->releasePointer(true);
            return;
        }
        foreach ($places as $place) {
            if ("anywhere" === $place) {
                $this->setPointer($id, true, $active);
                $this->releasePointer(true);
                return;
            }
            if (!preg_match("/^(before|after|start|end):/", $place)){
                $place = "after:$place";
            }

            list ($place, $targetId) = explode(':', $place, 2);
            $explodedTargetId = explode('|', $targetId);
            $lastIdPart = array_pop($explodedId);
            $atBorder = "start" !== $place && "end" !== $place;
            $lastTargetPart =
                $atBorder
                    ? array_pop($explodedTargetId)
                    : $explodedTargetId[sizeof($explodedTargetId) - 1];
            if ($explodedId === $explodedTargetId) {
                // $id can be placed near specified $targetId
                if (!empty($explodedTargetId)) {
                    $this->setPointer(
                        implode('|', $explodedTargetId),
                        !$atBorder,
                        $active
                    );
                }
                if (!is_array($this->pointer)) {
                    $this->pointer = [];
                }
                $keys = array_keys($this->pointer);
                $index = array_search($lastTargetPart, $keys);
                $element = [
                    $lastIdPart => $this->getElement($lastTargetPart)
                ];
                if ($index === false && $atBorder) {
                    continue;
                }
                switch ($place) {
                    case "after":
                        $this->pointer =
                            $index === (sizeof($this->pointer) - 1)
                            ? $this->pointer + $element
                            : (
                                array_slice(
                                    $this->pointer, 0, $index + 1, true
                                ) + $element +
                                array_slice(
                                    $this->pointer,
                                    $index + 1,
                                    sizeof($this->pointer) - $index - 1,
                                    true
                                )
                            );
                        break;
                    case "start":
                        $this->pointer = $element + $this->pointer;
                        $this->setPointer($id, false, $active);
                        break;
                    case "end":
                        $this->pointer += $element;
                        $this->setPointer($id, false, $active);
                        break;
                    case "before":
                        $this->pointer =
                            $index === 0
                            ? $element + $this->pointer
                            : (
                                array_slice($this->pointer, 0, $index, true) +
                                $element +
                                array_slice(
                                    $this->pointer,
                                    $index,
                                    sizeof($this->pointer) - $index,
                                    true
                                )
                            );
                        break;
                }
                $this->pointer = &$this->pointer[$lastIdPart];
                $this->releasePointer(true);
                return;
            }
        }
        if ($this->developerMode) {
            $this->releasePointer(false);
            Loader::onError(
                sprintf("DEBUG: Cannot add tab '$id' at '%s'", implode("/", $places)),
                "InvalidPlaceException"
            );
        } else {
            $this->setPointer($id, true, $active);
            $this->releasePointer(true);
        }
    }

    /**
     * Send data to the tab.
     *
     * @param  mixed $data  Data to send
     * @return void
     */
    public function send($data)
    {
        $this->current->send($data);
    }

    /**
     * Returns tabs.
     *
     * @return array
     */
    public function get()
    {
        return $this->content;
    }

    /**
     * Returns tabs captions.
     *
     * @return array
     */
    public function getCaptions()
    {
        return $this->captions;
    }

    /**
     * Returns last tab name.
     *
     * @return string
     */
    public function getLast()
    {
        return $this->last;
    }

    /**
     * Returns new tab element
     *
     * @param  string $name
     * @param  bool   $active
     * @param  bool   $disabled
     * @return Node
     */
    protected function getElement($name, $active = false, $disabled = false)
    {
        return new Node($name, $active, $disabled);
    }

    /**
     * Returns pointer or checks name duplication
     *
     * @param string $id
     * @param bool $create  When need to create
     * @param bool $active  Activity flag
     * @return bool  true if created or found
     */
    protected function setPointer($id, $create = false, $active = false)
    {
        $ids = explode('|', $id);
        $this->pointer = &$this->content;
        $lastIndex = sizeof($ids) - 1;
        $tab = [];
        foreach ($ids as $index => $id) {
            if ($this->checkDisabled) {
                $tab[] = $id;
                $tabPart = implode('|', $tab);
                if (in_array($tabPart, $this->disabledTabs)) {
                    // Set pointer to the empty tab element
                    $this->pointer = &$this->emptyElement;
                    return true;
                }
            }
            if (is_object($this->pointer)) {
                // If element content isn't empty, return element
                $this->pointer =
                    $this->pointer->get() !== ''
                    ? [$this->pointer->getName() => $this->pointer]
                    : [];
            }
            if (isset($this->pointer[$id])) {
                $this->pointer = &$this->pointer[$id];
            } else {
                if ($create) {
                    $this->pointer[$id] =
                        $index < $lastIndex
                            ? []
                            : $this->getElement(
                                $id,
                                $active//,
//                                $this->_checkDisabled &&
//                                in_array(
//                                    $tabPart,
//                                    $this->_disabledTabs['client']
//                                )
                            );
                    $this->pointer = &$this->pointer[$id];
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Cleanup pointer reference
     *
     * @param  bool  $setCurrent  Set current tab pointer on success
     * @return void
     */
    protected function releasePointer($setCurrent)
    {
        if ($setCurrent) {
            $this->current = $this->pointer;
        }
        // Cleanup reference
        unset($this->pointer);
    }
}
