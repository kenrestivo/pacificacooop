import cgi

class Page:
    """ TODO: move this somewhere senisible"""
    debug = []
    output = []
    headers = []
    forminput = dict()

    def __init__(self):
        """hack around bug in cgi input"""
        self.forminput = dict([(i.name, i.value)
                               for i in cgi.FieldStorage(keep_blank_values=True).list])
    def add_header(self, line):
        self.headers.append(line)


    def add_output(self, line):
        self.output.append(line)


    def add_debug(self, line):
        self.debug.append(line)


    def add_line(self, line, type):
        getattr(self, type).append(line)

    def render(self, debug = False):
        for i in  self.headers:
            print i
        print
        if debug:
            for i in self.debug:
                print i
        for i in self.output:
            print i
