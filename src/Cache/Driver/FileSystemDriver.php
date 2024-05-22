<?php
/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */

namespace VM\Cache\Driver;

use VM\Exception\SystemException;

/**
 * [Class FileSystem]
 *
 * @package VM\Cache
 */
class FileSystemDriver extends Driver
{

    use RetrievesMultipleKeys;

    /**
     * 版本
     * @var string
     */
    private $ver = '$Rev$';

    /**
     * 缓存文件
     * @var
     */
    private $file;

    /**
     * 默认缓存大小
     * @var string
     */
    private $cache_size = '15M';

    /**
     * 节点大小
     * @var int
     */
    private $idx_node_size = 40;

    /**
     * 40+20+24*16+16*16*16*16*4;
     * @var int
     */
    private $data_base_pos = 262588;

    /**
     * 结构大小
     * @var int
     */
    private $schema_item_size = 24;

    /**
     * 保留空间 放置php标记防止下载
     * @var int
     */
    private $header_padding = 20;

    /**
     * 保留空间 4+16 maxsize|ver
     * @var int
     */
    private $info_size = 20;

    /**
     * id 计数器节点地址
     * 40起 添加20字节保留区域
     *
     * @var int
     */
    private $idx_seq_pos = 40;

    /**
     * id 计数器节点地址
     * @var int
     */
    private $file_curr_pos = 44;

    /**
     * id 空闲链表入口地址
     * @var int
     */
    private $idx_free_pos = 48;

    /**
     * 40+20+24*16
     * @var int
     */
    private $idx_base_pos = 444;

    /**
     * 节点基点
     * @var
     */
    private $idx_node_base = 0;

    /**
     * 10M最小值
     * @var int
     */
    private $min_size = 10240;

    /**
     * 最大值
     * @var
     */
    private $max_size;

    /**
     * 结构
     * @var array
     */
    private $schema_module = array('size','free','lru_head','lru_tail','hits','miss');


    /**
     * 资源缓存
     * @var
     */
    private $resource;

    /**
     * 文件size列表
     * @var array
     */
    private $size_list = [];


    /**
     * 锁定块大小
     * @var array
     */
    private $block_size_list = [];


    /**
     * 节点构建
     * @var array
     */
    private $node_module =[
        'next'=>[0,'V'],
        'prev'=>[4,'V'],
        'data'=>[8,'V'],
        'size'=>[12,'V'],
        'lru_right'=>[16,'V'],
        'lru_left'=>[20,'V'],
        'key'=>[24,'H*']
    ];


    /**
     * 缓存构造
     *
     * @param null $file
     */
    public function __construct($file = null)
    {

        $this->file = sprintf('%s', hash('crc32',$file)).'.php';

        $this->size_list = array(512=>10, 3<<10=>10, 8<<10=>10, 20<<10=>4, 30<<10=>2, 50<<10=>2, 80<<10=>2, 96<<10=>2, 128<<10=>2, 224<<10=>2, 256<<10=>2, 512<<10=>1, 1024<<10=>1);

        $this->load();
    }

    /**
     * [initialize 系统初始化]
     *
     * @return bool
     * @author 11.
     */
    private function load()
    {
        $this->file = config('dir.cache', runtime('cache'));

        if(!file_exists($this->file))
        {
            $this->resource = fopen($this->file,'wb+');
            if(!is_resource($this->resource))
            {
                throw new SystemException('Can\'t open the cache file: '.realpath($this->file));
            }

            fseek($this->resource,0);
            fputs($this->resource,'<?php exit()?>');

            return $this->initialize();

        }else{

            $this->resource = fopen($this->file, 'rb+');
            if(!is_resource($this->resource))
            {
                throw new SystemException('Can\'t open the cache file: '.realpath($this->file));
            }
            $this->seek($this->header_padding);
            $info = unpack('V1max_size/a*ver', fread($this->resource, $this->info_size));

            if(trim($info['ver']) != $this->ver)
            {
                $this->initialize(true);
            }else{
                $this->max_size = $info['max_size'];
            }
        }

        $this->idx_node_base = $this->data_base_pos + $this->max_size;

        $this->block_size_list = array_keys($this->size_list);

        sort($this->block_size_list);

        return true;
    }


    /**
     * [lock 文件加锁]
     * 如果flock不管用，请继承本类，并重载此方法
     * @param            $is_block [是否阻塞]
     * @param bool|false $whatever
     * @author 11.
     */
    private function lock($is_block, $whatever = false)
    {
        ignore_user_abort(1);
        return flock($this->resource, $is_block ? LOCK_EX : LOCK_EX+LOCK_NB);
    }

    /**
     * [unlock 解锁文件]
     * 如果flock不管用，请继承本类，并重载此方法
     * @return bool
     * @author 11.
     */
    private function unlock()
    {
        ignore_user_abort(0);
        return flock($this->resource, LOCK_UN);
    }


    /**
     * [seek 移动指针]
     *
     * @param $offset
     * @author 11.
     */
    private function seek($offset)
    {
        return fseek($this->resource, $offset);
    }


    /**
     * [put 置入函数]
     *
     * @param $offset
     * @param $source
     * @return int
     * @author 11.
     */
    private function push($offset, $source)
    {
        if($offset < $this->max_size*1.5)
        {
            $this->seek($offset);
            $d = fputs($this->resource, $source);
        }else{
            throw new SystemException('Offset over quota:'. $offset);
        }
    }


    /**
     * [FormatCacheFile]
     *
     * @param bool|false $truncate
     * @return bool
     * @author 11.
     */
    private function initialize($truncate = false)
    {
        if($this->lock(true,true))
        {
            if($truncate){
                $this->seek(0);
                ftruncate($this->resource, $this->idx_node_base);
            }

            $this->max_size = $this->parse_str_size($this->cache_size,15728640);

            $this->push($this->header_padding, pack('V1a*', $this->max_size, $this->ver));

            ksort($this->size_list);

            $ds_offset = $this->data_base_pos;
            $i = 0;
            foreach ($this->size_list as $size => $count) {
                /**
                 * 将预分配的空间注册到free链表里
                 */
                $count *= min(3, floor($this->max_size/10485760));

                $next_free_node = 0;

                for($j=0;$j<$count;$j++){
                    $this->push($ds_offset,pack('V',$next_free_node));
                    $next_free_node = $ds_offset;
                    $ds_offset+=intval($size);
                }
                $code = pack(str_repeat('V1',count($this->schema_module)),$size,$next_free_node,0,0,0,0);

                $this->push(60+$i*$this->schema_item_size,$code);
                $i++;
            }
            $this->set_current_pos($ds_offset);
            $this->push($this->idx_base_pos, str_repeat("\0", 262144));
            $this->push($this->idx_seq_pos, pack('V',1));
            $this->unlock();

            return true;
        }else{
            throw new SystemException("Couldn't lock the file !" );
        }

    }

    /**
     * [set_current_pos 设置当前游标]
     *
     * @param $pos
     * @return int
     * @author 11.
     */
    private function set_current_pos($pos)
    {
        return $this->push($this->file_curr_pos, pack('V', $pos));
    }

    /**
     * [get_current_pos 获取当前游标]
     *
     * @return mixed
     * @author 11.
     */
    private function get_current_pos()
    {
        $this->seek($this->file_curr_pos);
        list(, $ds_offset) = unpack('V', fread($this->resource, 4));
        return $ds_offset;
    }

    /**
     * [parse_str_size 格式化字符串大小]
     *
     * @author 11.
     */
    private function parse_str_size($str_size, $default)
    {
        if(!preg_match('/^([0-9]+)\s*([gmk]|)$/i',$str_size,$match)) return $default;

        switch(strtolower($match[2])){
            case 'g':
                if($match[1]>1){
                   throw new SystemException ('Max cache size 1G');
                }
                $size = $match[1]<<30;
                break;
            case 'm':
                $size = $match[1]<<20;
                break;
            case 'k':
                $size = $match[1]<<10;
                break;
            default:
                $size = $match[1];
        }
        if($size<=0){
            throw new SystemException('Error cache size '.$this->max_size);
        }elseif($size<10485760){
            return 10485760;
        }else{
            return $size;
        }
    }

    /**
     * [dir 获取当前缓存目录名]
     *
     * @return string
     */
    public function dir()
    {
        return pathinfo($this->file, PATHINFO_DIRNAME);
    }

    /**
     * [get 获取缓存]
     *
     * @param      $key [缓存键名]
     * @param null $value [缓存数据,如果不存在缓存则写入]
     * @return bool|mixed|null|string
     * @author 11.
     */
    public function get($key, &$value = null)
    {
        if(!$this->has($key, $offset))
        {
            if($value){

                $this->set($key, $value);
                return $value;
            }

            if($this->lock(false)) $this->unlock();
            return false;
        }

        $info = $this->get_node($offset);
        $schema_id = $this->get_size_schema_id($info['size']);

        if($schema_id === false){
            if($this->lock(false)) $this->unlock();
            return false;
        }
        $this->seek($info['data']);

        $source = fread($this->resource, $info['size']);

        if(($data = unserialize($source))) return $data;

        return $source;
    }


    /**
     * [set 设置缓存]
     *
     * @param string $key
     * @param mixed $value
     * @param int $time
     * @return bool
     * @author 11.
     */
    public function set($key, $value, $time = 0)
    {
        if(!$this->lock(true)) throw new SystemException('Can\'t lock the file!');

        $value = serialize($value);

        $size = strlen($value);

        $has = $this->has($key, $list_idx_offset);

        $schema_id = $this->get_size_schema_id($size);

        if($schema_id === false)
        {
            $this->unlock();
            return false;
        }

        if($has)
        {
            $hd_sequel = $list_idx_offset;

            $info = $this->get_node($hd_sequel);

            if($schema_id == $this->get_size_schema_id($info['size']))
            {
                $data_offset = $info['data'];
            }else{
                //清除原有lru
                $this->lru_delete($info);

                if(!($data_offset = $this->allocate($schema_id)))
                {
                    $this->unlock();
                    $this->set_schema($schema_id, 'miss', $this->get_schema($schema_id, 'miss')+1);
                    return false;
                }

                $this->free_current_space($info['size'], $info['data']);
                $this->set_node($hd_sequel, 'lru_left', 0);
                $this->set_node($hd_sequel, 'lru_right', 0);
            }

            $this->set_node($hd_sequel, 'size', $size);
            $this->set_node($hd_sequel, 'data', $data_offset);
        }else{

            if(!($data_offset = $this->allocate($schema_id)))
            {
                $this->unlock();
                $this->set_schema($schema_id, 'miss', $this->get_schema($schema_id, 'miss')+1);
                return false;
            }

            $key = crc32((trim($key)));

            $hd_sequel = $this->allocate_idx(array(
                'next'=>0,
                'prev'=>$list_idx_offset,
                'data'=>$data_offset,
                'size'=>$size,
                'lru_right'=>0,
                'lru_left'=>0,
                'key'=>$key
            ));

            if($list_idx_offset>0)
            {
                $this->set_node($list_idx_offset, 'next', $hd_sequel);
            }else{
                $this->set_node_root($key, $hd_sequel);
            }

        }

        if($data_offset > $this->max_size) throw new SystemException('allocate data size:'. $data_offset);

        $this->push($data_offset, $value);
        $this->set_schema($schema_id, 'hits', $this->get_schema($schema_id, 'hits')+1);
        $this->lru_push($schema_id, $hd_sequel);
        $this->unlock();

        return true;
    }

    /**
     * [del 缓存删除方法]
     *
     * @param            $key
     * @param bool|false $pos
     * @return mixed
     * @author 11.
     */
    public function del($key, $pos = false)
    {
        if($pos || $this->has($key, $pos))
        {
            if($info = $this->get_node($pos))
            {
                $key = crc32(trim($key));
                //删除data区域
                if($info['prev'])
                {
                    $this->set_node($info['prev'], 'next', $info['next']);
                    $this->set_node($info['next'], 'prev', $info['prev']);
                }else{
                    //改入口位置
                    $this->set_node($info['next'], 'prev', 0);
                    $this->set_node_root($key, $info['next']);
                }
                $this->free_current_space($info['size'], $info['data']);
                $this->lru_delete($info);
                $this->free_node($pos);
                return $info['prev'];
            }
        }
        return false;
    }

    /**
     * [has 判断缓存是否存在]
     * 如果找到节点则$pos=节点本身 返回true 否则 $pos=树的末端 返回false
     * @param $key
     * @param $pos
     * @return bool
     * @author 11.
     */
    public function has($key, &$pos = 0)
    {
        $key = crc32(trim($key));
        return $this->get_pos_by_key($this->get_node_root($key), $key, $pos);
    }


    public function save($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * [increment 递增]
     *
     * @param string $key
     * @param int $value
     * @return bool
     */
    public function increment($key, $value = 1)
    {
       return $this->set($key, intval($this->get($key)) + $value);
    }


    /**
     * [decrement 递减]
     *
     * @param string $key
     * @param int $value
     * @return bool|int
     */
    public function decrement($key, $value = 1)
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * [flush 清空所有缓存]
     *
     * @return mixed
     */
    public function flush()
    {
       return make('file')->deleteDirectory($this->dir(), true);
    }


    public function prefix()
    {
        // TODO: Implement prefix() method.
    }

    /**
     * [status 获取缓存状态]
     *
     * @return array
     * @author 11.
     */
    public function status()
    {
        $schema_status = $this->status_schema();

        $free_Bytes = $this->max_size - $this->get_current_pos();

        $miss = $hits = 0;

        foreach($schema_status as $schema)
        {
            $schema['free_count'] = isset($schema['free_count'])? $schema['free_count'] : 0;
            $free_Bytes += $schema['free_count'] * $schema['size'];

            $miss += $schema['miss'];
            $hits += $schema['hits'];
        }
        return array('hits'=>$hits, 'miss'=>$miss);
    }

    /**
     * [set_schema 设置结构]
     *
     * @param $schema_id
     * @param $key
     * @param $value
     * @return int
     * @author 11.
     */
    private function set_schema($schema_id, $key, $value)
    {
        $source = array_flip($this->schema_module);

        return $this->push(60 + $schema_id * $this->schema_item_size + $source[$key] * 4, pack('V', $value));
    }

    /**
     * [get_schema 获取结构]
     *
     * @param $id
     * @param $key
     * @return mixed
     * @author 11.
     */
    private function get_schema($id, $key)
    {
        $source = array_flip($this->schema_module);

        $this->seek(60 + $id * $this->schema_item_size);

        unpack('V1'.implode('/V1', $this->schema_module), fread($this->resource, $this->schema_item_size));

        $this->seek(60 + $id * $this->schema_item_size + $source[$key] * 4);

        list(, $value) = unpack('V', fread($this->resource, 4));

        return $value;
    }

    private function all_schema()
    {
        $schemas = [];
        for($i = 0; $i<16; $i++)
        {
            $this->seek(60 + $i * $this->schema_item_size);
            $info = unpack('V1'. implode('/V1', $this->schema_module), fread($this->resource, $this->schema_item_size));
            if($info['size'])
            {
                $info['id'] = $i;
                $schemas[$i] = $info;
            }else{
                return $schemas;
            }
        }
    }

    /**
     * [status_schema 结构状态]
     *
     * @return array
     * @author 11.
     */
    private function status_schema()
    {
        $status = [];
        foreach($this->all_schema() as $k => $schema)
        {
            if($schema['free'])
            {
                $this->follow($schema['free'], $schema['free_count']);
            }
            $status[] = $schema;
        }
        return $status;
    }


    /**
     * [get_size_schema_id 获取架构ID]
     *
     * @param $size
     * @return bool|int|string
     * @author 11.
     */
    private function get_size_schema_id($size)
    {
        foreach( $this->block_size_list as $k => $block_size )
        {
            if($size <= $block_size) return $k;
        }

        return false;
    }


    /**
     * [follow 跟踪节点]
     *
     * @param $pos
     * @param $i
     * @return mixed
     * @author 11.
     */
    private function follow($pos, &$i)
    {
        $i++;
        $this->seek($pos);
        list(, $next) = unpack('V1', fread($this->resource, 4));
        if($next)
        {
            return $this->follow($next, $i);
        }else{
            return $pos;
        }
    }

    /**
     * [set_node 设置节点]
     *
     * @param $pos
     * @param $key
     * @param $value
     * @return bool|int
     * @author 11.
     */
    private function set_node($pos, $key, $value)
    {
        if(!$pos) return false;

        if(isset($this->node_module[$key]))
        {
            return $this->push($pos * $this->idx_node_size + $this->idx_node_base + $this->node_module[$key][0], pack($this->node_module[$key][1], $value));
        }else{
            return false;
        }
    }

    /**
     * [set_node_root 设置根节点]
     *
     * @param $key
     * @param $value
     * @return int
     * @author 11.
     */
    private function set_node_root($key, $value)
    {
        return $this->push(hexdec(substr($key, 0, 4)) * 4 + $this->idx_base_pos, pack('V', $value));
    }

    /**
     * [get_node 或取节点]
     *
     * @param $offset
     * @return array
     * @author 11.
     */
    private function get_node($offset)
    {
        $this->seek($offset * $this->idx_node_size + $this->idx_node_base);

        $info = unpack('V1next/V1prev/V1data/V1size/V1lru_right/V1lru_left/H*key',fread($this->resource, $this->idx_node_size));

        $info['offset'] = $offset;

        return $info;
    }

    /**
     * [get_node_root 获取根节点]
     *
     * @param $key
     * @return mixed
     * @author 11.
     */
    private function get_node_root($key)
    {
        $this->seek(hexdec(substr($key, 0, 4)) * 4 + $this->idx_base_pos);
        list(, $offset) = unpack('V', fread($this->resource, 4));
        return $offset;
    }


    /**
     * [get_pos_by_key 根据key获取指针]
     *
     * @param $offset
     * @param $key
     * @param $pos
     * @author 11.
     */
    private function get_pos_by_key($offset, $key, &$pos)
    {
        if(!$offset)
        {
            $pos = 0;

            return false;
        }

        $info = $this->get_node($offset);

        if($info['key'] == $key)
        {
            $pos = $info['offset'];
            return true;
        }else if($info['next'] && $info['next'] != $offset)
        {
            return $this->get_pos_by_key($info['next'], $key, $pos);
        }else{
            $pos = $offset;
            return false;
        }

    }


    /**
     * [allocate 分配空间]
     *
     * @param            $schema_id
     * @param bool|false $lru_freed
     * @return mixed
     * @author 11.
     */
    private function allocate($schema_id, $lru_freed = false)
    {
        //如果LRU里有链表
        if($free = $this->get_schema($schema_id, 'free'))
        {
            $this->seek($free);
            list(,$next) = unpack('V', fread($this->resource, 4));
            $this->set_schema($schema_id, 'free', $next);
            return $free;
        }elseif($lru_freed){
            throw new SystemException('Bat Lru was pop free size');
        }else{
            $ds_offset = $this->get_current_pos();
            $size = $this->get_schema($schema_id, 'size');

            if($size + $ds_offset > $this->max_size)
            {
                if($info = $this->lru_pop($schema_id))
                {
                    return $this->allocate($schema_id, $info);
                }else{
                    throw new SystemException('Can\'t allocate data space');
                }
            }else{
                $this->set_current_pos($ds_offset);
                return $ds_offset;
            }
        }
    }

    /**
     * [allocate_idx 分配IDX]
     *
     * @param $data
     * @return mixed
     * @author 11.
     */
    private function allocate_idx($data)
    {
        $this->seek($this->idx_free_pos);
        list(, $list_pos) = unpack('V', fread($this->resource, 4));
        if($list_pos)
        {
            $this->seek($list_pos * $this->idx_node_size + $this->idx_node_base);
            list(, $prev_free_node) = unpack('V', fread($this->resource, 4));

            $this->push($this->idx_free_pos, pack('V', $prev_free_node));
        }else{
            $this->seek($this->idx_seq_pos);
            list(, $list_pos) = unpack('V', fread($this->resource, 4));
            $this->push($this->idx_seq_pos, pack('V', $list_pos+1));
        }

        return $this->create_node($list_pos, $data);
    }

    /**
     * [free_current_space 释放当前空间]
     *
     * @param $size
     * @param $pos
     * @author 11.
     */
    private function free_current_space($size, $pos)
    {
        if($pos > $this->max_size) throw new SystemException('free space over quota:'. $pos);

        $schema_id = $this->get_size_schema_id($size);
        if($free = $this->get_schema($schema_id, 'free'))
        {
            $this->push($free, pack('V1', $pos));
        }else{
            $this->set_schema($schema_id, 'free', $pos);
        }
        $this->push($pos, pack('V1', 0));
    }


    /**
     * [create_node 创建节点]
     *
     * @param $pos
     * @param $data
     * @return mixed
     * @author 11.
     */
    private function create_node($pos, $data)
    {
        $this->push($pos * $this->idx_node_size+ $this->idx_node_base,
            pack('V1V1V1V1V1V1H*', $data['next'], $data['prev'], $data['data'], $data['size'], $data['lru_right'], $data['lru_left'], $data['key']));
        return $pos;
    }


    /**
     * [free_node 释放节点]
     *
     * @param $pos
     * @author 11.
     */
    private function free_node($pos)
    {
        $this->seek($this->idx_free_pos);

        list(, $prev_free_node) = unpack('V', fread($this->resource, 4));

        $this->push($pos, $this->idx_node_base + $this->idx_node_size, pack('V', $prev_free_node). str_repeat("\0", $this->idx_node_size-4));

        return $this->push($this->idx_free_pos, pack('V', $pos));
    }


    /**
     * [lru_pop 弹出LRU]
     *
     * @param $schema_id
     * @return array|bool
     * @author 11.
     */
    private function lru_pop($schema_id)
    {
        if(!$node = $this->get_schema($schema_id, 'lru_tail')) return false;

            $info = $this->get_node($node);
            if(!$info['data']) return false;


        $this->del($info['key'], $info['offset']);

        if(!$this->get_schema($schema_id, 'free'))
        {
            throw new SystemException('Pop Lru, But Nothing free.');
        }

        return $info;
    }

    /**
     * [lru_push LRU压入]
     *
     * @param $schema_id
     * @param $offset
     * @return bool|void
     * @author 11.
     */
    private function lru_push($schema_id, $offset)
    {
        $lru_head = $this->get_schema($schema_id, 'lru_head');
        $lru_tail = $this->get_schema($schema_id, 'lru_tail');

        if(!$offset || $lru_head == $offset) return;

        $info = $this->get_node($offset);
        $this->set_node($info['lru_right'],'lru_left',$info['lru_left']);
        $this->set_node($info['lru_left'],'lru_right',$info['lru_right']);
        $this->set_node($offset,'lru_right',$lru_head);
        $this->set_node($offset,'lru_left',0);
        $this->set_node($lru_head,'lru_left',$offset);
        $this->set_schema($schema_id,'lru_head',$offset);

        if($lru_tail == 0)
        {
            $this->set_schema($schema_id, 'lru_tail', $offset);
        }elseif($lru_tail == $offset && $info['lru_left']){
            $this->set_schema($schema_id, 'lru_tail', $info['lru_left']);
        }
        return true;
    }

    /**
     * [lru_delete LRU删除功能]
     *
     * @param $source
     * @return bool
     * @author 11.
     */
    private function lru_delete($source)
    {
        if($source['lru_right'])
        {
            $this->set_node($source['lru_right'], 'lru_left', $source['lru_left']);
        }else{
            $this->set_schema($this->get_size_schema_id($source['size']), 'lru_tail', $source['lru_left']);
        }

        if($source['lru_left'])
        {
            $this->set_node($source['lru_left'], 'lru_right', $source['lru_right']);
        }else{
            $this->set_schema($this->get_size_schema_id($source['size']), 'lru_head', $source['lru_right']);
        }

        return true;
    }



}