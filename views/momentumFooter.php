	
	</div>
	
	<div id="footer">
		<p>
			<a href="http://validator.w3.org/check?uri=referer">XHTML 1.0</a>
			<a href="http://jigsaw.w3.org/css-validator/validator?uri=http://www.maryndesign.com<?= $_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] != '' ? "?" . urlencode($_SERVER['QUERY_STRING']) . "&amp;view=" . $_SESSION['view'] : "?view=" . $_SESSION['view']); ?>">CSS 2.1</a>
			<a href="http://www.feedvalidator.org">RSS 2.0</a>
		</p>
	</div>
</div>

</body>
</html>