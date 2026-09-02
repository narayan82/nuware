"""Reviewed borderless-table cell boundaries, in PDF points (1-based pages).

Explicit profiles avoid guessing row/column associations from flattened PDF text.
Each entry: page, column boundaries, row boundaries, optional header labels.
"""
TABLES = {
 'ai-data-sciences-nl': [(2,[72,214,540],[698,715,732,749,766],['Category','Technology Stack']), (3,[72,214,540],[72,106,124],None)],
 'ai-scaling-agentic-solutions': [(3,[72,236,540],[732,749,768],['Impact Area','Outcome']), (4,[72,236,540],[72,106,140,157,174,191],None)],
 'application-crm': [(3,[72,201,540],[223,240,257,274,291,309,326,343,360],['Category','Technologies'])],
 'application-global-analytics': [(2,[72,224,540],[663,680,714,731,749],['Category','Technology Stack']), (3,[72,224,540],[72,105],None)],
 'applications-l1l2-application-dba-support-nutrition-client': [(4,[72,136,205,458,478,504,530],[157,237,288,322,373,407,460],['Environment','Type','Services / Applications','Prod','Non-Prod','Total'])],
 'cloud-devops': [(3,[72,209,540],[155,172,189,206,223,240,275],['Category','Tools/Technologies'])],
 'data-compliance-system': [(3,[72,176,540],[342,359,376,393,410,428],['Component','Technology Stack'])],
 'fintech-market-edm': [(2,[72,233,540],[745,763],['Category','Technologies / Tools']), (3,[72,233,540],[72,106,123,140,175],None)],
 'healthcare-noc-helpdesk-support': [(2,[48,145,554],[253,270,287,321,338,355,372,407,424,480],['Parameter','Details'])],
 'infra-advisory-consulting-services': [(3,[72,210,540],[309,326,360,394,428,463],['Category','Details']), (4,[72,218,540],[337,354,371,388,422,456,475],['Category','Impact'])],
 'retail-nutri-atg': [(3,[72,240,540],[638,655,689,706,723,742],['Category','Impact']), (4,[72,240,540],[72,108],None)],
 'retail-nutri-oms': [(4,[72,201,540],[524,541,575,592,609,643,678],['Category','Impact'])],
}
TABLES['fintech-study-nlp'] = TABLES['ai-data-sciences-nl']
TABLES['fintech-compliance-trade-surveillance-solutions'] = TABLES['data-compliance-system']
