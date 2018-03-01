Pulp-LiveReload
===

```php
$p->task('less', function() use($p) {
	$lr = new \Pulp\LiveReload();
	$lr->listen($p->loop);

	$p->watch( ['foo/**/*.less'])->on('change', function($file) use ($p, $lr) {
		$p->output("File changed '".$file->getFilename()."'");
		$p->src(['foo/bootstrap.less'])
		  ->pipe(new \Pulp\Less( ['compress'=>TRUE] ))
		  ->pipe($p->dest('foo/compressed.css'))
		  ->pipe($lr)
	  	;

		//you can call the plugin's fileChanged method
		//directly or you can pipe it after $p->dest()
	    //$lr->fileChanged('foo/compressed.css');
	});
});
```

Add to your web page

```html
	<script type="text/javascript"  src="http://localhost:35729/livereload.js"></script>
```
