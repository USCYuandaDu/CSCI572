import java.io.IOException;
import java.util.StringTokenizer;
import java.util.*;
import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.io.IntWritable;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;

public class WordCount {
	    //mapper class
        public static class TokenizerMapper
        extends Mapper<Object, Text, Text, Text>{
                private Text word = new Text();
                private Text docId = new Text();
                public void map(Object key, Text value, Context context
                                ) throws IOException, InterruptedException {
                	    String line = value.toString();
                        StringTokenizer itr = new StringTokenizer(line.split("\t")[1]);
                        docId.set(line.split("\t")[0]);
                        while (itr.hasMoreTokens()) {
                                word.set(itr.nextToken());
                                context.write(word, docId);
                        }
                }
        }
        //reducer class
        public static class IntSumReducer
        extends Reducer<Text,Text,Text,Text> {
                private Text finalMap = new Text();
                public void reduce(Text key, Iterable<Text> values,
                                Context context
                                ) throws IOException, InterruptedException {
                	    HashMap<String, Integer> myMap = new HashMap<String, Integer>();
                        for (Text val : values) {
                        	if(myMap.containsKey(val.toString())){
                                myMap.put(val.toString(),myMap.get(val.toString())+1);
                        }else{
                                myMap.put(val.toString(),1);
                        }
                }
                StringBuilder output = new StringBuilder();
                for(Map.Entry<String, Integer> pair : myMap.entrySet()){
                	output.append(pair.getKey());
                	output.append(":");
                	output.append(pair.getValue());
                	output.append("\t");
                }
                finalMap.set(output.toString());
                context.write(key, finalMap);
        }
}
public static void main(String[] args) throws Exception {
	    //driver
        Configuration conf = new Configuration();
        Job job = Job.getInstance(conf, "word count");
        job.setJarByClass(WordCount.class);
        job.setMapperClass(TokenizerMapper.class);
        job.setReducerClass(IntSumReducer.class);
        job.setOutputKeyClass(Text.class);
        job.setOutputValueClass(Text.class);
        FileInputFormat.addInputPath(job, new Path(args[0]));
        FileOutputFormat.setOutputPath(job, new Path(args[1]));
        System.exit(job.waitForCompletion(true) ? 0 : 1);
}
}
