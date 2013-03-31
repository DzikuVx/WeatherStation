function print_r(o) {
	function f(o, p, s) {
		for (x in o) {
			if ('object' == typeof o[x]) {
				s += p + x + ' obiekt: \n';
				pre = p + '\t';
				s = f(o[x], pre, s);
			} else {
				s += p + x + ' : ' + o[x] + '\n';
			}
		}
		return s;
	}
	return f(o, '', '');
}