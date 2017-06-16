package assignment;

import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.IOException;
import java.io.PrintWriter;
import java.util.HashSet;
import java.util.LinkedHashMap;
import java.util.Set;

public class ExtractLinks {

	public static void main(String[] args) throws IOException {
		// TODO Auto-generated method stub
		String csvFile = "F:/ws/IR_Assignment4/src/assignment/LATimesData/mapLATimesDataFile.csv";
		BufferedReader br = new BufferedReader(new FileReader(csvFile));
		LinkedHashMap<String,String> map1 = new LinkedHashMap<String,String>();
		LinkedHashMap<String,String> map2 = new LinkedHashMap<String,String>();
		String line = "";
		while((line = br.readLine()) != null){
			String[] mapPair = line.split(",");
			map1.put(mapPair[0], mapPair[1]);
			map2.put(mapPair[1], mapPair[0]);
		}
		br.close();
		File dir = new File("F:/ws/IR_Assignment4/src/assignment/LATimesData/LATimesDownloadData/");
		Set<String> edges = new HashSet<String>();
		for(File file: dir.listFiles()){
			Document doc = Jsoup.parse(file, "UTF-8", map1.get(file.getName()));
			Elements links = doc.select("a[href]");
		        
		    for(Element link : links){
		        String url = link.attr("abs:href").trim();
		        if(map2.containsKey(url)){
		        	edges.add(file.getName() + " " + map2.get(url));
		        }

		    }
		}

		try{
		    PrintWriter writer = new PrintWriter("edgesList.txt", "UTF-8");
		    for(String s: edges){
		    	writer.println(s);
		    }
		    writer.flush();
		    writer.close();
		} catch (IOException e) {
		   // do something
		}

	}

}
