-- Create Classes Table
CREATE TABLE public.classes (
    id uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    name text NOT NULL,
    grade_level text NOT NULL,
    created_at timestamp with time zone DEFAULT now()
);

-- Create Teachers Table
CREATE TABLE public.teachers (
    id uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    name text NOT NULL,
    department text NOT NULL,
    email text UNIQUE NOT NULL,
    status text DEFAULT 'Active',
    created_at timestamp with time zone DEFAULT now()
);

-- Create Students Table
CREATE TABLE public.students (
    id uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    name text NOT NULL,
    class_id uuid REFERENCES public.classes(id) ON DELETE SET NULL,
    parent_contact text NOT NULL,
    status text DEFAULT 'Active',
    created_at timestamp with time zone DEFAULT now()
);

-- Enable Row Level Security (RLS)
ALTER TABLE public.classes ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.teachers ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.students ENABLE ROW LEVEL SECURITY;

-- Create Policies allowing authenticated users full access
CREATE POLICY "Allow full access to authenticated users on classes" ON public.classes
    FOR ALL
    TO authenticated
    USING (true)
    WITH CHECK (true);

CREATE POLICY "Allow full access to authenticated users on teachers" ON public.teachers
    FOR ALL
    TO authenticated
    USING (true)
    WITH CHECK (true);

CREATE POLICY "Allow full access to authenticated users on students" ON public.students
    FOR ALL
    TO authenticated
    USING (true)
    WITH CHECK (true);
