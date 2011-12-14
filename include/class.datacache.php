<?php
class datacache {

        private $cachekey;
        private $m;

        public function __construct($key) {
		global $scfg,$dd_pct,$dd_vid;
   
                if (empty($key)) return false;
                // ��֤�� ���þɵİ�
                //if( (strpos($key,'56_validate_code_')===false) &&  isset($dd_pct) && isset($dd_vid) && $dd_pct==1 && (($dd_vid %10) <10)){ // �µ�
                       //  header('new:hit'); // ���� ���þ�ɾ����
                        $this->cachekey = $key;
                        if (!$this->m) {
                                $this->m = new Memcached();
                                $this->m->setOption(Memcached::OPT_DISTRIBUTION, Memcached::DISTRIBUTION_CONSISTENT);
                                $this->m->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, TRUE );
                                $this->m->setOption(Memcached::OPT_HASH, Memcached::HASH_CRC );
                                $this->m->setOption(Memcached::OPT_PREFIX_KEY, 'st_' );
                                $this->m->setOption(Memcached::OPT_COMPRESSION, TRUE );
                                $this->m->setOption(Memcached::OPT_NO_BLOCK, TRUE );
                                $this->m->setOption(Memcached::OPT_TCP_NODELAY, TRUE );
                                $this->m->addServers($scfg["memcached"]);	
                        }
                /*}else{  // �ɵ�
                        header('old:hit');  // ���� ���þ�ɾ����
                        $this->cachekey = 'cm_' . $key;
                        if (!$this->m) {
                                $this->m = new Memcache;
                                foreach($scfg["memcache"] as $v)			
    				$this->m->addServer($v[0], $v[1], $v[2], $v[3]);
                                $this->m->setCompressThreshold(20000, 0.2);	
                        }
                }*/
        }

        public function get() {
                return $this->m->get($this->cachekey);
        }

        public function put($val,$expire=2592000) {
                return $this->m->set($this->cachekey,$val,intval($expire));
        }

        public function del() {
                return $this->m->delete($this->cachekey);
        }

	public function incr($value=1) {
                return $this->m->increment($this->cachekey,intval($value));
	}

	public function decr($value=1) {
                return $this->m->decrement($this->cachekey,intval($value));
	}

	public function status() {
		return $this->m->getStats();
	}

	public function getResultCode() {
		return $this->m->getResultCode();
	}

	public function getResultMessage() {
		return $this->m->getResultMessage();
	}
}
